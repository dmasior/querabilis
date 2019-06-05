<?php declare(strict_types=1);

namespace Tests\Contract\Driver;

use Initx\Driver\SqsQueue;
use Initx\Exception\NoSuchElementException;
use Tests\ContractTester;
use Tests\Double\EnvelopeMother;
use Tests\Double\SqsClientMother;
use Tests\Double\SqsQueueMother;

/**
 * @coversDefaultClass \Initx\Driver\SqsQueue
 */
class SnsQueueCest
{
    /**
     * @var string
     */
    private $queueName;

    public function _before(): void
    {
        $this->queueName = SqsQueueMother::createQueue();
    }

    public function _after(): void
    {
        SqsQueueMother::dropTestQueues();
    }

    public function addTwo(ContractTester $I): void
    {
        // arrange
        $queue = new SqsQueue(SqsClientMother::default(), $this->queueName);

        // act
        $queue->add(EnvelopeMother::any());
        $queue->add(EnvelopeMother::any());
    }

    public function offerTwo(ContractTester $I): void
    {
        // arrange
        $queue = new SqsQueue(SqsClientMother::default(), $this->queueName);

        // act
        $queue->offer(EnvelopeMother::any());
        $queue->offer(EnvelopeMother::any());
    }

    public function remove(ContractTester $I): void
    {
        // arrange
        $envelopeOne = EnvelopeMother::any();
        $queue = new SqsQueue(SqsClientMother::default(), $this->queueName);
        $queue->add($envelopeOne);

        // act
        $actualOne = $queue->remove();

        // assert
        $I->assertEquals($envelopeOne, $actualOne);
    }

    public function removeOnEmptyQueueThrows(ContractTester $I): void
    {
        // arrange
        $queue = new SqsQueue(SqsClientMother::default(), $this->queueName);

        // act
        $I->expectException(NoSuchElementException::class, function () use ($queue) {
            $queue->remove();
        });
    }

    public function poll(ContractTester $I): void
    {
        // arrange
        $envelope = EnvelopeMother::any();
        $queue = new SqsQueue(SqsClientMother::default(), $this->queueName);
        $queue->add($envelope);

        // act
        $actual = $queue->poll();

        // assert
        $I->assertEquals($envelope, $actual);
    }

    public function pollReturnNull(ContractTester $I): void
    {
        // arrange
        $queue = new SqsQueue(SqsClientMother::default(), $this->queueName);

        // act
        $actual = $queue->poll();

        // assert
        $I->assertNull($actual);
    }

    public function peek(ContractTester $I): void
    {
        // arrange
        $envelope = EnvelopeMother::any();
        $queue = new SqsQueue(SqsClientMother::default(), $this->queueName);
        $queue->add($envelope);

        // act
        $actual = $queue->peek();

        // assert
        $I->assertEquals($envelope, $actual);
    }

    public function peekReturnNull(ContractTester $I): void
    {
        // arrange
        $queue = new SqsQueue(SqsClientMother::default(), $this->queueName);

        // act
        $actual = $queue->peek();

        // assert
        $I->assertNull($actual);
    }

    public function element(ContractTester $I): void
    {
        // arrange
        $envelope = EnvelopeMother::any();
        $queue = new SqsQueue(SqsClientMother::default(), $this->queueName);
        $queue->add($envelope);

        // act
        $actual = $queue->element();

        // assert
        $I->assertEquals($envelope, $actual);
    }

    public function elementThrows(ContractTester $I): void
    {
        // arrange
        $queue = new SqsQueue(SqsClientMother::default(), $this->queueName);

        // act
        $I->expectException(NoSuchElementException::class, function () use ($queue) {
            $queue->element();
        });
    }
}
