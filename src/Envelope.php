<?php declare(strict_types=1);

namespace Initx;

use DateTime;
use DateTimeInterface;

final class Envelope
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $payload;

    /**
     * @var DateTimeInterface
     */
    private $timestamp;

    public function __construct(string $payload, ?string $title = null, ?DateTimeInterface $timestamp = null)
    {
        $this->title = $title ?: bin2hex(random_bytes(7));
        $this->timestamp = $timestamp ?: new DateTime();
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getPayload(): string
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
