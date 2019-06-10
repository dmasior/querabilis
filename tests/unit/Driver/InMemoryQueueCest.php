<?php declare(strict_types=1);

namespace Initx\Querabilis\Tests\Unit\Driver;

use Codeception\Example;
use Initx\Querabilis\Driver\InMemoryQueue;
use Initx\Querabilis\Exception\NoSuchElementException;
use Initx\Querabilis\Tests\Double\EnvelopeMother;
use Initx\Querabilis\Tests\UnitTester;

class InMemoryQueueCest
{
    /**
     * @example { "method": "add" }
     * @example { "method": "offer" }
     */
    public function addAndOffer(UnitTester $I, Example $example): void
    {
        $method = $example['method'];
        $envelopeOne = EnvelopeMother::any();
        $envelopeTwo = EnvelopeMother::any();
        $queue = new InMemoryQueue();

        $resultOne = $queue->$method($envelopeOne);
        $resultTwo = $queue->$method($envelopeTwo);

        $I->assertSame($envelopeOne, $queue->remove());
        $I->assertSame($envelopeTwo, $queue->poll());
        $I->assertTrue($resultOne);
        $I->assertTrue($resultTwo);
    }

    /**
     * @example { "method": "remove" }
     * @example { "method": "element" }
     */
    public function throwsOnEmptyQueue(UnitTester $I, Example $example): void
    {
        $method = $example['method'];
        $queue = new InMemoryQueue();

        $I->expectException(NoSuchElementException::class, function () use ($queue, $method): void {
            $queue->$method();
        });
    }

    /**
     * @example { "method": "element" }
     * @example { "method": "peek" }
     */
    public function elementAndPeek(UnitTester $I, Example $example): void
    {
        $method = $example['method'];
        $envelopeOne = EnvelopeMother::any();
        $queue = new InMemoryQueue();
        $queue->add($envelopeOne);

        $I->assertSame($envelopeOne, $queue->$method());
    }
}
