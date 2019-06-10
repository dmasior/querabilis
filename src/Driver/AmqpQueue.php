<?php declare(strict_types=1);

namespace Initx\Querabilis\Driver;

use Initx\Querabilis\Envelope;
use Initx\Querabilis\Exception\IllegalStateException;
use Initx\Querabilis\Queue;
use JMS\Serializer\SerializerInterface;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AbstractConnection;
use PhpAmqpLib\Message\AMQPMessage;

final class AmqpQueue implements Queue
{
    use HasFallbackSerializer;
    use HasDefaultRemoveAndElement;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $exchange;

    /**
     * @var string
     */
    private $routingKey;

    /**
     * @var string
     */
    private $queue;

    /**
     * @var AMQPChannel
     */
    private $channel;

    public function __construct(
        AbstractConnection $connection,
        string $queue,
        string $exchange = '',
        string $routingKey = '',
        ?SerializerInterface $serializer = null
    ) {
        $this->channel = $connection->channel();
        $this->queue = $queue;
        $this->exchange = $exchange;
        $this->routingKey = $routingKey;
        $this->serializer = $this->fallbackSerializer($serializer);
    }

    public function add(Envelope $envelope): bool
    {
        if (!$this->offer($envelope)) {
            throw new IllegalStateException("Could not write to AMQP");
        }

        return true;
    }

    public function offer(Envelope $envelope): bool
    {
        $serialized = $this->serializer->serialize($envelope, 'json');
        $message = new AMQPMessage($serialized);

        $this->channel->basic_publish($message, $this->exchange, $this->routingKey);

        return true;
    }

    public function poll(): ?Envelope
    {
        $message = $this->channel->basic_get($this->queue, true);

        if (!$message) {
            return null;
        }

        return $this->serializer->deserialize($message->body, Envelope::class, 'json');
    }

    public function peek(): ?Envelope
    {
        $message = $this->channel->basic_get($this->queue);

        if (!$message) {
            return null;
        }

        $this->channel->basic_nack($message->get('delivery_tag'), false, true);

        return $this->serializer->deserialize($message->body, Envelope::class, 'json');
    }
}
