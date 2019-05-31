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

    public function addOk(IntegrationTester $I)
    {
        $key = 'add';
        $envelope = EnvelopeMother::any();
        $queue = new RedisQueue(PredisClientMother::default(), $key);

        $queue->add($envelope);

        $I->seeInRedis($key);
    }

    public function offerOk(IntegrationTester $I)
    {
        $key = 'offer';
        $envelope = EnvelopeMother::any();
        $queue = new RedisQueue(PredisClientMother::default(), $key);

        $queue->offer($envelope);

        $I->seeInRedis($key);
    }

    public function removeOk(IntegrationTester $I)
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
}
