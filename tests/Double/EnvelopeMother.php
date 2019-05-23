<?php declare(strict_types=1);

namespace Initx\Tests\Double;

use Initx\Envelope;

class EnvelopeMother
{
    public static function any(): Envelope
    {
        $payload = PayloadMother::any();

        return new Envelope($payload);
    }
}
