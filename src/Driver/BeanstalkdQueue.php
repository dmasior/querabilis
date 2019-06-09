<?php declare(strict_types=1);

namespace Initx\Querabilis\Driver;

use Initx\Querabilis\Envelope;
use Initx\Querabilis\Exception\IllegalStateException;
use Initx\Querabilis\Exception\NoSuchElementException;
use Initx\Querabilis\Queue;
use JMS\Serializer\SerializerInterface;
use Pheanstalk\Contract\PheanstalkInterface;

final class BeanstalkdQueue implements Queue
{
    use HasFallbackSerializer;

    /**
     * @var PheanstalkInterface
     */
    private $client;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $queueName;

    public function __construct(
        PheanstalkInterface $client,
        string $queueName = PheanstalkInterface::DEFAULT_TUBE,
        ?SerializerInterface $serializer = null
    ) {
        $this->client = $client;
        $this->queueName = $queueName;
        $this->serializer = $this->fallbackSerializer($serializer);
    }

    public function add(Envelope $envelope): bool
    {
        if (!$this->offer($envelope)) {
            throw new IllegalStateException('Could not write to redis');
        }

        return true;
    }

    public function offer(Envelope $envelope): bool
    {
        $serialized = $this->serializer->serialize($envelope, 'json');

        return (bool)$this->client
            ->useTube($this->queueName)
            ->put($serialized);
    }

    public function remove(): Envelope
    {
        $element = $this->poll();

        if (!$element) {
            throw new NoSuchElementException();
        }

        return $element;
    }

    public function poll(): ?Envelope
    {
        $job = $this->client
            ->watch($this->queueName)
            ->reserveWithTimeout(0);

        if (empty($job)) {
            return null;
        }

        $serialized = $job->getData();

        if (empty($serialized)) {
            return null;
        }

        return $this->serializer->deserialize($serialized, Envelope::class, 'json');
    }

    public function element(): Envelope
    {
        $element = $this->peek();

        if (!$element) {
            throw new NoSuchElementException();
        }

        return $element;
    }

    public function peek(): ?Envelope
    {
        $job = $this->client
            ->watch($this->queueName)
            ->peekReady();

        if (empty($job)) {
            return null;
        }

        $serialized = $job->getData();

        if (empty($serialized)) {
            return null;
        }

        return $this->serializer->deserialize($serialized, Envelope::class, 'json');
    }
}
