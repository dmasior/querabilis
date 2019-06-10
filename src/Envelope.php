<?php declare(strict_types=1);

namespace Initx\Querabilis;

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

    public function __construct(string $payload, ?string $title = null, ?DateTime $timestamp = null)
    {
        $this->payload = $payload;
        $this->title = $title ?: \bin2hex(\random_bytes(7));
        $this->timestamp = $timestamp ?: new DateTime();
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }
}
