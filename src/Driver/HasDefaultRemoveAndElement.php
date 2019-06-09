<?php declare(strict_types=1);

namespace Initx\Querabilis\Driver;

use Initx\Querabilis\Envelope;
use Initx\Querabilis\Exception\NoSuchElementException;

trait HasDefaultRemoveAndElement
{
    public function remove(): Envelope
    {
        return $this->returnOrThrowNoSuchElement($this->poll());
    }

    public function element(): Envelope
    {
        return $this->returnOrThrowNoSuchElement($this->peek());
    }

    private function returnOrThrowNoSuchElement(?Envelope $envelope = null): Envelope
    {
        if (!$envelope) {
            throw new NoSuchElementException();
        }

        return $envelope;
    }
}
