<?php declare(strict_types=1);

namespace Initx\Driver;

use Aws\Result;
use Aws\Sqs\SqsClient;
use Initx\Envelope;
use Initx\Exception\IllegalStateException;
use Initx\Exception\NoSuchElementException;
use Initx\Queue;
use JMS\Serializer\SerializerInterface;
use Ramsey\Uuid\Uuid;

final class SqsQueue implements Queue
{
    use HasFallbackSerializer;

    /**
     * @var SqsClient
     */
    private $client;

    /**
     * @var string
     */
    private $queueName;

    /**
     * @var string
     */
    private $queueUrl = '';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(SqsClient $client, string $queueName, ?SerializerInterface $serializer = null)
    {
        $this->client = $client;
        $this->queueName = $queueName;
        $this->serializer = $this->fallbackSerializer($serializer);
    }

    public function add(Envelope $envelope): bool
    {
        if (!$this->offer($envelope)) {
            throw new IllegalStateException("Could not add to queue $this->queueName");
        }

        return true;
    }

    public function offer(Envelope $envelope): bool
    {
        $this->resolveQueueUrl();

        $serialized = $this->serializer->serialize($envelope, 'json');
        $args = [
            'QueueUrl' => $this->queueUrl,
            'MessageBody' => $serialized,
        ];

        if ($this->isFifo()) {
            $args['MessageGroupId'] = __CLASS__;
            $args['MessageDeduplicationId'] = Uuid::uuid4()->toString();
        }

        return (bool)$this->client->sendMessage($args);
    }

    public function remove(): Envelope
    {
        $envelope = $this->poll();

        if (!$envelope) {
            throw new NoSuchElementException();
        }

        return $envelope;
    }

    public function poll(): ?Envelope
    {
        $this->resolveQueueUrl();
        $result = $this->queryForOneMessage();

        if ($result->get('Messages') && count($result->get('Messages'))) {
            $message = $result->get('Messages')[0];
            $envelope = $this->serializer->deserialize($message['Body'], Envelope::class, 'json');

            // remove message from queue
            $this->client->deleteMessage([
                'QueueUrl' => $this->queueUrl,
                'ReceiptHandle' => $message['ReceiptHandle'],
            ]);

            return $envelope;
        }

        // no messages
        return null;
    }

    public function element(): Envelope
    {
        $envelope = $this->peek();

        if (!$envelope) {
            throw new NoSuchElementException();
        }

        return $envelope;
    }

    public function peek(): ?Envelope
    {
        $this->resolveQueueUrl();
        $result = $this->queryForOneMessage();

        if ($result->get('Messages') && count($result->get('Messages'))) {
            $message = $result->get('Messages')[0];
            $envelope = $this->serializer->deserialize($message['Body'], Envelope::class, 'json');

            return $envelope;
        }

        // no messages
        return null;
    }

    private function resolveQueueUrl(): void
    {
        if (empty($this->queueUrl)) {
            $result = $this->client->getQueueUrl(['QueueName' => $this->queueName]);
            $this->queueUrl = $result->get('QueueUrl');
            if (empty($this->queueUrl)) {
                throw new IllegalStateException("Could not resolve queue url from queue name '{$this->queueName}'");
            }
        }
    }

    private function queryForOneMessage(): Result
    {
        return $this->client->receiveMessage([
            'MaxNumberOfMessages' => 1,
            'QueueUrl' => $this->queueUrl,
        ]);
    }

    private function isFifo(): bool
    {
        return $this->endsWith($this->queueName, '.fifo');
    }

    private function endsWith(string $haystack, string $needle): bool
    {
        $length = strlen($needle);

        return (substr($haystack, -$length) === $needle);
    }
}
