<?php declare(strict_types=1);

namespace Initx\Driver;

use Initx\Envelope;
use Initx\Exception\IllegalStateException;
use Initx\Exception\NoSuchElementException;
use Initx\Queue;
use JMS\Serializer\SerializerInterface;
use Pheanstalk\Contract\PheanstalkInterface;

/**
 * Class BeanstalkdQueue
 *
 * @package Initx\Driver
 */
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

    /**
     * BeanstalkdQueue constructor.
     *
     * @param PheanstalkInterface      $client
     * @param string                   $queueName
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        PheanstalkInterface $client,
        string $queueName = PheanstalkInterface::DEFAULT_TUBE,
        ?SerializerInterface $serializer = null
    ) {
        $this->client = $client;
        $this->queueName = $queueName;
        $this->serializer = $this->fallbackSerializer($serializer);
    }

    /**
     * Inserts an element if possible, otherwise throwing exception.
     *
     * @param Envelope $envelope
     * @return bool
     * @throws IllegalStateException
     */
    public function add(Envelope $envelope): bool
    {
        if (!$this->offer($envelope)) {
            throw new IllegalStateException('Could not write to redis');
        }

        return true;
    }

    /**
     * Inserts an element if possible, otherwise returning false.
     *
     * @param Envelope $envelope
     * @return bool
     */
    public function offer(Envelope $envelope): bool
    {
        $serialized = $this->serializer->serialize($envelope, 'json');

        return (bool)$this->client
            ->useTube($this->queueName)
            ->put($serialized);
    }

    /**
     * Remove and return head of queue, otherwise throwing exception.
     *
     * @return Envelope
     * @throws NoSuchElementException
     */
    public function remove(): Envelope
    {
        $element = $this->poll();

        if (!$element) {
            throw new NoSuchElementException();
        }

        return $element;
    }

    /**
     * Remove and return head of queue, otherwise returning null.
     *
     * @return Envelope | null
     */
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

    /**
     * Return but do not remove head of queue, otherwise throwing exception.
     *
     * @return Envelope
     * @throws NoSuchElementException
     */
    public function element(): Envelope
    {
        $element = $this->peek();

        if (!$element) {
            throw new NoSuchElementException();
        }

        return $element;
    }

    /**
     * Return but do not remove head of queue, otherwise returning null.
     *
     * @return Envelope | null
     */
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
