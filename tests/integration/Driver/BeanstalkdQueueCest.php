<?php declare(strict_types=1);

namespace Initx\Querabilis\Tests\Integration\Driver;

use Initx\Querabilis\Driver\BeanstalkdQueue;
use Pheanstalk\Contract\PheanstalkInterface;
use Initx\Querabilis\Tests\Double\EnvelopeMother;
use Initx\Querabilis\Tests\Double\BeanstalkdClientMother;
use Initx\Querabilis\Tests\IntegrationTester;

class BeanstalkdQueueCest
{
    public function _before(IntegrationTester $I): void
    {
        $I->clearBeanstalkTube(BeanstalkdClientMother::default());
    }

    public function add(IntegrationTester $I): void
    {
        $envelope = EnvelopeMother::any();
        $pheanstalk = BeanstalkdClientMother::default();
        $queue = new BeanstalkdQueue($pheanstalk, PheanstalkInterface::DEFAULT_TUBE);

        $actual = $queue->add($envelope);

        $I->seeBeanstalkQueueHasCurrentCount($pheanstalk, 1);
        $I->seeBeanstalkCurrentEnvelope($pheanstalk, $envelope);
        $I->assertTrue($actual);
    }

    public function offer(IntegrationTester $I): void
    {
        $envelope = EnvelopeMother::any();
        $pheanstalk = BeanstalkdClientMother::default();
        $queue = new BeanstalkdQueue($pheanstalk, PheanstalkInterface::DEFAULT_TUBE);

        $queue->offer($envelope);

        $I->seeBeanstalkQueueHasCurrentCount($pheanstalk, 1);
        $I->seeBeanstalkCurrentEnvelope($pheanstalk, $envelope);
    }

    public function remove(IntegrationTester $I): void
    {
        $pheanstalk = BeanstalkdClientMother::default();
        $queue = new BeanstalkdQueue($pheanstalk, PheanstalkInterface::DEFAULT_TUBE);

        $envelopeOne = EnvelopeMother::any();
        $envelopeTwo = EnvelopeMother::any();

        $queue->add($envelopeOne);
        $queue->add($envelopeTwo);

        $actualOne = $queue->remove();
        $actualTwo = $queue->remove();

        $I->assertEquals($envelopeOne, $actualOne);
        $I->assertEquals($envelopeTwo, $actualTwo);
        $I->seeBeanstalkQueueHasCurrentCount($pheanstalk, 0);
    }

    public function poll(IntegrationTester $I): void
    {
        $pheanstalk = BeanstalkdClientMother::default();
        $queue = new BeanstalkdQueue($pheanstalk, PheanstalkInterface::DEFAULT_TUBE);

        $envelopeOne = EnvelopeMother::any();
        $envelopeTwo = EnvelopeMother::any();

        $queue->add($envelopeOne);
        $queue->add($envelopeTwo);

        $actualOne = $queue->poll();
        $actualTwo = $queue->poll();
        $actualThree = $queue->poll();

        $I->assertEquals($envelopeOne, $actualOne);
        $I->assertEquals($envelopeTwo, $actualTwo);
        $I->assertNull($actualThree);
        $I->seeBeanstalkQueueHasCurrentCount($pheanstalk, 0);
    }

    public function peek(IntegrationTester $I): void
    {
        $pheanstalk = BeanstalkdClientMother::default();
        $queue = new BeanstalkdQueue($pheanstalk, PheanstalkInterface::DEFAULT_TUBE);

        $envelopeOne = EnvelopeMother::any();
        $envelopeTwo = EnvelopeMother::any();

        $queue->add($envelopeOne);
        $queue->add($envelopeTwo);

        $actualOne = $queue->peek();
        $actualTwo = $queue->peek();

        $I->assertEquals($envelopeOne, $actualOne);
        // actual two = envelope one
        $I->assertEquals($envelopeOne, $actualTwo);
        $I->seeBeanstalkQueueHasCurrentCount($pheanstalk, 2);
    }

    public function element(IntegrationTester $I): void
    {
        $pheanstalk = BeanstalkdClientMother::default();
        $queue = new BeanstalkdQueue($pheanstalk, PheanstalkInterface::DEFAULT_TUBE);

        $envelopeOne = EnvelopeMother::any();
        $envelopeTwo = EnvelopeMother::any();

        $queue->add($envelopeOne);
        $queue->add($envelopeTwo);

        $actualOne = $queue->element();
        $actualTwo = $queue->element();

        $I->assertEquals($envelopeOne, $actualOne);
        // actual two = envelope one
        $I->assertEquals($envelopeOne, $actualTwo);
        $I->seeBeanstalkQueueHasCurrentCount($pheanstalk, 2);
    }
}
