<?php declare(strict_types=1);

namespace Tests\Unit\Driver;

use Codeception\Example;
use Initx\Exception\NoSuchElementException;
use Tests\Double\EnvelopeMother;
use Tests\UnitTester;
use Initx\Driver\InMemoryQueue;

class InMemoryQueueCest
{
    /**
     * @example { "method": "add" }
     * @example { "method": "offer" }
     */
    public function addAndOffer(UnitTester $I, Example $example)
    {
        $method = $example['method'];
        $envelopeOne = EnvelopeMother::any();
        $envelopeTwo = EnvelopeMother::any();
        $queue = new InMemoryQueue();

        $queue->$method($envelopeOne);
        $queue->$method($envelopeTwo);

        $I->assertSame($envelopeOne, $queue->remove());
        $I->assertSame($envelopeTwo, $queue->poll());
    }

    /**
     * @example { "method": "remove" }
     * @example { "method": "element" }
     */
    public function throwsOnEmptyQueue(UnitTester $I, Example $example)
    {
        $method = $example['method'];
        $queue = new InMemoryQueue();

        $I->expectException(NoSuchElementException::class, function () use ($queue, $method) {
            $queue->$method();
        });
    }

    /**
     * @example { "method": "element" }
     * @example { "method": "peek" }
     */
    public function elementAndPeek(UnitTester $I, Example $example)
    {
        $method = $example['method'];
        $envelopeOne = EnvelopeMother::any();
        $queue = new InMemoryQueue();
        $queue->add($envelopeOne);

        $I->assertSame($envelopeOne, $queue->$method());
    }
}
