<?php declare(strict_types=1);

namespace Initx\Querabilis\Tests\Unit;

use DateTime;
use Initx\Querabilis\Envelope;
use Initx\Querabilis\Tests\UnitTester;

class EnvelopeCest
{
    public function newInstance(UnitTester $I): void
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
