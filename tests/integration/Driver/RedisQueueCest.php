<?php declare(strict_types=1);

namespace Tests\Integration\Driver;

use Initx\Driver\RedisQueue;
use Tests\Double\EnvelopeMother;
use Tests\Double\PredisClientMother;
use Tests\IntegrationTester;

class RedisQueueCest
{
    public function _before(IntegrationTester $I)
    {
        $I->sendCommandToRedis('FLUSHALL');
    }

    public function add(IntegrationTester $I)
    {
        $key = 'add';
        $envelope = EnvelopeMother::any();
        $queue = new RedisQueue(PredisClientMother::default(), $key);

        $queue->add($envelope);

        $I->seeInRedis($key);
    }

    public function offer(IntegrationTester $I)
    {
        $key = 'offer';
        $envelope = EnvelopeMother::any();
        $queue = new RedisQueue(PredisClientMother::default(), $key);

        $queue->offer($envelope);

        $I->seeInRedis($key);
    }

    public function remove(IntegrationTester $I)
    {
        $key = 'remove';
        $envelopeOne = EnvelopeMother::any();
        $envelopeTwo = EnvelopeMother::any();
        $queue = new RedisQueue(PredisClientMother::default(), $key);
        $queue->add($envelopeOne);
        $queue->add($envelopeTwo);

        $actualOne = $queue->remove();
        $actualTwo = $queue->remove();

        $I->assertEquals($envelopeOne, $actualOne);
        $I->assertEquals($envelopeTwo, $actualTwo);
        $I->dontSeeInRedis($key);
    }

    public function poll(IntegrationTester $I)
    {
        $key = 'poll';
        $envelopeOne = EnvelopeMother::any();
        $envelopeTwo = EnvelopeMother::any();
        $queue = new RedisQueue(PredisClientMother::default(), $key);
        $queue->add($envelopeOne);
        $queue->add($envelopeTwo);

        $actualOne = $queue->poll();
        $actualTwo = $queue->poll();
        $actualThree = $queue->poll();

        $I->assertEquals($envelopeOne, $actualOne);
        $I->assertEquals($envelopeTwo, $actualTwo);
        $I->assertNull($actualThree);
        $I->dontSeeInRedis($key);
    }

    public function peek(IntegrationTester $I)
    {
        $key = 'peek';
        $envelopeOne = EnvelopeMother::any();
        $envelopeTwo = EnvelopeMother::any();
        $queue = new RedisQueue(PredisClientMother::default(), $key);
        $queue->add($envelopeOne);
        $queue->add($envelopeTwo);

        $actualOne = $queue->peek();
        $actualTwo = $queue->peek();

        $I->assertEquals($envelopeOne, $actualOne);
        // actual two = envelope one
        $I->assertEquals($envelopeOne, $actualTwo);
        $I->seeInRedis($key);
    }

    public function element(IntegrationTester $I)
    {
        $key = 'element';
        $envelopeOne = EnvelopeMother::any();
        $envelopeTwo = EnvelopeMother::any();
        $queue = new RedisQueue(PredisClientMother::default(), $key);
        $queue->add($envelopeOne);
        $queue->add($envelopeTwo);

        $actualOne = $queue->element();
        $actualTwo = $queue->element();

        $I->assertEquals($envelopeOne, $actualOne);
        // actual two = envelope one
        $I->assertEquals($envelopeOne, $actualTwo);
        $I->seeInRedis($key);
    }
}
