<?php declare(strict_types=1);

namespace Initx\Querabilis\Driver;

use Initx\Querabilis\Envelope;
use Initx\Querabilis\Exception\IllegalStateException;
use Initx\Querabilis\Exception\NoSuchElementException;
use Initx\Querabilis\Queue;
use JMS\Serializer\SerializerInterface;
use Predis\ClientInterface;
use Throwable;

final class RedisQueue implements Queue
{
    use HasFallbackSerializer;
    use HasDefaultRemoveAndElement;

    /**
     * @var ClientInterface
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

    public function __construct(ClientInterface $client, string $queueName, ?SerializerInterface $serializer = null)
    {
        $this->client = $client;
        $this->queueName = $queueName;
        $this->serializer = $this->fallbackSerializer($serializer);
    }

    public function add(Envelope $envelope): bool
    {
        if (!$this->offer($envelope)) {
            throw new IllegalStateException("Could not write to redis");
        }

        return true;
    }

    public function offer(Envelope $envelope): bool
    {
        $serialized = $this->serializer->serialize($envelope, 'json');

        return (bool)$this->client->rpush(
            $this->queueName,
            [$serialized]
        );
    }

    public function poll(): ?Envelope
    {
        try {
            $serialized = $this->client->lpop($this->queueName);
        } catch (Throwable $e) {
            throw new IllegalStateException("Predis connection error", 0, $e);
        }

        if (empty($serialized)) {
            return null;
        }

        return $this->serializer->deserialize($serialized, Envelope::class, 'json');
    }

    public function peek(): ?Envelope
    {
        $serialized = $this->client->lrange($this->queueName, 0, 0)[0];

        if (empty($serialized)) {
            return null;
        }

        return $this->serializer->deserialize($serialized, Envelope::class, 'json');
    }
}
