<?php declare(strict_types=1);

namespace Initx\Querabilis\Driver;

use Initx\Querabilis\Envelope;
use Initx\Querabilis\Exception\NoSuchElementException;
use Initx\Querabilis\Queue;

final class InMemoryQueue implements Queue
{
    /**
     * @var Envelope[]
     */
    private $items = [];

    public function add(Envelope $envelope): bool
    {
        return $this->offer($envelope);
    }

    public function offer(Envelope $envelope): bool
    {
        $this->items[] = $envelope;

        return true;
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
        $item = array_shift($this->items);

        return $item ?: null;
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
        $item = $this->items[0] ?? null;

        return $item;
    }
}
