<?php declare(strict_types=1);

namespace Initx\Driver;

use Initx\Envelope;
use Initx\Exception\IllegalStateException;
use Initx\Exception\NoSuchElementException;
use Initx\Queue;
use JMS\Serializer\SerializerInterface;
use Predis\Client;
use Predis\ClientInterface;
use Throwable;

class RedisQueue implements Queue
{
    use HasFallbackSerializer;

    /**
     * @var Client
     */
    private $client;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var string
     */
    private $queue;

    public function __construct(ClientInterface $client, string $queue, ?SerializerInterface $serializer = null)
    {
        $this->client = $client;
        $this->serializer = $serializer;
        $this->queue = $queue;
        $this->fallbackSerializer();
    }

    public function add(Envelope $envelope): void
    {
        if (!$this->offer($envelope)) {
            throw new IllegalStateException("Could not write to redis");
        }
    }

    public function offer(Envelope $envelope): bool
    {
        $serialized = $this->serializer->serialize($envelope, 'json');

        return (bool)$this->client->rpush(
            $this->queue,
            [$serialized]
        );
    }

    public function remove(): Envelope
    {
        $element = $this->poll();

        if (!$element) {
            throw new NoSuchElementException('Queue empty');
        }

        return $element;
    }

    public function poll(): ?Envelope
    {
        try {
            $serialized = $this->client->lpop($this->queue);
        } catch (Throwable $e) {
            throw new IllegalStateException("Predis connection error", 0, $e);
        }

        if (empty($serialized)) {
            return null;
        }

        return $this->serializer->deserialize($serialized, Envelope::class, 'json');
    }

    public function element(): Envelope
    {
        // TODO: Implement element() method.
    }

    public function peek(): ?Envelope
    {
        // TODO: Implement peek() method.
    }
}
