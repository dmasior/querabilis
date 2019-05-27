<?php declare(strict_types=1);

namespace Initx;

use Initx\Exception\IllegalStateException;
use Initx\Exception\NoSuchElementException;

interface Queue
{
    /**
     * Inserts an element if possible, otherwise throwing exception.
     *
     * @param Envelope $envelope
     * @return void
     * @throws IllegalStateException
     */
    public function add(Envelope $envelope): void;

    /**
     * Inserts an element if possible, otherwise returning null.
     *
     * @param Envelope $envelope
     * @return void|null
     */
    public function offer(Envelope $envelope): void;

    /**
     * Remove and return head of queue, otherwise throwing exception.
     *
     * @return Envelope
     * @throws NoSuchElementException
     */
    public function remove(): Envelope;

    /**
     * Remove and return head of queue, otherwise returning null.
     *
     * @return Envelope | null
     */
    public function poll(): ?Envelope;

    /**
     * Return but do not remove head of queue, otherwise throwing exception.
     *
     * @return Envelope
     * @throws NoSuchElementException
     */
    public function element(): Envelope;

    /**
     * Return but do not remove head of queue, otherwise returning null.
     *
     * @return Envelope | null
     */
    public function peek(): ?Envelope;
}