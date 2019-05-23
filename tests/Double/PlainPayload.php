<?php declare(strict_types=1);

namespace Initx\Tests\Double;

use Initx\Payload;

class PlainPayload implements Payload
{
    /**
     * @var string
     */
    private $content;

    public function __construct(string $content)
    {
        $this->content = $content;
    }

    public function __toString(): string
    {
        return $this->content;
    }
}
