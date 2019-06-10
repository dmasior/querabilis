<?php declare(strict_types=1);

namespace Initx\Querabilis\Driver;

use Initx\Querabilis\Envelope;
use Initx\Querabilis\Queue;

final class InMemoryQueue implements Queue
{
    use HasDefaultRemoveAndElement;

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

    public function poll(): ?Envelope
    {
        $item = array_shift($this->items);

        return $item ?: null;
    }

    public function peek(): ?Envelope
    {
        return $this->items[0] ?? null;
    }
}
