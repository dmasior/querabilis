<?php declare(strict_types=1);

namespace Initx\Querabilis;

use Initx\Querabilis\Exception\IllegalStateException;
use Initx\Querabilis\Exception\NoSuchElementException;

interface Queue
{
    /**
     * Inserts an element if possible, otherwise throwing exception.
     *
     * @param Envelope $envelope
     * @return bool
     * @throws IllegalStateException
     */
    public function add(Envelope $envelope): bool;

    /**
     * Inserts an element if possible, otherwise returning false.
     *
     * @param Envelope $envelope
     * @return bool
     */
    public function offer(Envelope $envelope): bool;

    /**
     * Remove and return head of queue, otherwise throwing exception.
     *
     * @return Envelope
     * @throws IllegalStateException | NoSuchElementException
     */
    public function remove(): Envelope;

    /**
     * Remove and return head of queue, otherwise returning null.
     *
     * @return Envelope | null
     * @throws IllegalStateException
     */
    public function poll(): ?Envelope;

    /**
     * Return but do not remove head of queue, otherwise throwing exception.
     *
     * @return Envelope
     * @throws IllegalStateException | NoSuchElementException
     */
    public function element(): Envelope;

    /**
     * Return but do not remove head of queue, otherwise returning null.
     *
     * @return Envelope | null
     * @throws IllegalStateException
     */
    public function peek(): ?Envelope;
}
