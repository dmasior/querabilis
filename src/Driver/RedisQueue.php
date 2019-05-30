<?php declare(strict_types=1);

namespace Initx\Driver;

use Initx\Envelope;
use Initx\Exception\IllegalStateException;
use Initx\Exception\NoSuchElementException;
use Initx\Queue;
use JMS\Serializer\SerializerInterface;
use Predis\Client;

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
     * @param mixed $parameters Connection parameters. Docs: https://github.com/nrk/predis/wiki/Connection-Parameters
     * @param mixed $options Options to configure. Docs: https://github.com/nrk/predis/wiki/Client-Options
     * @param SerializerInterface|null $serializer
     */
    public function __construct($parameters = null, $options = null,  SerializerInterface $serializer = null)
    {
        $this->client = new Client($parameters, $options);
        $this->client->connect();
        $this->serializer = $serializer;
        $this->fallbackSerializer();
    }

    /**
     * @inheritDoc
     */
    public function add(Envelope $envelope): void
    {
        if (!$this->offer($envelope)) {
            throw new IllegalStateException("Could not write to redis");
        }
    }

    /**
     * @inheritDoc
     */
    public function offer(Envelope $envelope): bool
    {
        $serialized = $this->serializer->serialize($envelope, 'json');

        return (bool)$this->client->rpush(
            $envelope->getTitle(),
            [$serialized]
        );
    }

    /**
     * @inheritDoc
     */
    public function remove(): Envelope
    {
        // TODO: Implement remove() method.
    }

    /**
     * @inheritDoc
     */
    public function poll(): ?Envelope
    {
        // TODO: Implement poll() method.
    }

    /**
     * @inheritDoc
     */
    public function element(): Envelope
    {
        // TODO: Implement element() method.
    }

    /**
     * @inheritDoc
     */
    public function peek(): ?Envelope
    {
        // TODO: Implement peek() method.
    }
}
