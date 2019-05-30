<?php declare(strict_types=1);

namespace Initx;

use DateTime;

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
     * @var DateTime
     */
    private $timestamp;

    public function __construct(string $payload, string $title, ?DateTime $timestamp = null)
    {
        $this->payload = $payload;
        $this->title = $title;
        $this->timestamp = $timestamp ?: new DateTime();
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
     * @return DateTime
     */
    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }
}
