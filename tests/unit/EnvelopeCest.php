<?php declare(strict_types=1);

namespace Tests\Unit;

use DateTime;
use Initx\Querabilis\Envelope;
use Tests\UnitTester;

class EnvelopeCest
{
    public function newInstance(UnitTester $I)
    {
        // arrange
        $payload = 'some text';
        $title = 'any title';
        $timestamp = new DateTime();

        // act
        $envelope = new Envelope($payload, $title, $timestamp);

        // assert
        $I->assertSame($payload, $envelope->getPayload());
        $I->assertSame($title, $envelope->getTitle());
        $I->assertSame($timestamp, $envelope->getTimestamp());
    }
}
