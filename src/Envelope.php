<?php declare(strict_types=1);

namespace Initx;

use DateTimeImmutable;
use DateTimeInterface;

final class Envelope
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var Payload
     */
    private $payload;

    /**
     * @var DateTimeInterface
     */
    private $timestamp;

    public function __construct(Payload $payload, ?string $title = null, ?DateTimeInterface $timestamp = null)
    {
        $this->payload = $payload;
        $this->title = $title ?: bin2hex(random_bytes(7));
        $this->timestamp = $timestamp ?: new DateTimeImmutable();
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return Payload
     */
    public function getPayload(): Payload
    {
        return $this->payload;
    }

    /**
     * @return DateTimeInterface
     */
    public function getTimestamp(): DateTimeInterface
    {
        return $this->timestamp;
    }
}
