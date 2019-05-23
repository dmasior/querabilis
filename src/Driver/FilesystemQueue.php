<?php declare(strict_types=1);

namespace Initx\Driver;

use Initx\Envelope;
use Initx\Exception\IllegalStateException;
use Initx\Exception\NoSuchElementException;
use Initx\Queue;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Throwable;

final class FilesystemQueue implements Queue
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(string $path, SerializerInterface $serializer = null)
    {
        if (!$serializer) {
            $serializer = SerializerBuilder::create()->build();
        }
        $this->serializer = $serializer;
        $this->path = $path;
    }

    /**
     * Inserts an element if possible, otherwise throwing exception.
     *
     * @param Envelope $envelope
     * @return void
     * @throws IllegalStateException
     */
    public function add(Envelope $envelope): void
    {
        if (!$this->write($envelope)) {
            throw IllegalStateException::create("Could not write to {$this->path}");
        }
    }

    /**
     * Inserts an element if possible.
     *
     * @param Envelope $envelope
     * @return void
     */
    public function offer(Envelope $envelope): void
    {
        $this->write($envelope);
    }

    private function write(Envelope $envelope): bool
    {
        try {
            $result = (bool)file_put_contents(
                $this->path,
                $this->serializer->serialize($envelope, 'json'),
                FILE_APPEND
            );
        } catch (Throwable $exception) {
            $result = false;
        }

        return $result;
    }

    /**
     * Remove and return head of queue, otherwise throwing exception.
     *
     * @return Envelope
     * @throws NoSuchElementException
     */
    public function remove(): Envelope
    {
        // TODO: Implement remove() method.
    }

    /**
     * Remove and return head of queue, otherwise returning null.
     *
     * @return Envelope | null
     */
    public function poll(): ?Envelope
    {
        // TODO: Implement poll() method.
    }

    /**
     * Return but do not remove head of queue, otherwise throwing exception.
     *
     * @return Envelope
     * @throws NoSuchElementException
     */
    public function element(): Envelope
    {
        // TODO: Implement element() method.
    }

    /**
     * Return but do not remove head of queue, otherwise returning null.
     *
     * @return Envelope | null
     */
    public function peek(): ?Envelope
    {
        // TODO: Implement peek() method.
    }
}
