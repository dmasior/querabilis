<?php declare(strict_types=1);

namespace Tests\Integration\Driver;

use Initx\Driver\BeanstalkdQueue;
use Pheanstalk\Contract\PheanstalkInterface;
use Tests\Double\EnvelopeMother;
use Tests\Double\BeanstalkdClientMother;
use Tests\IntegrationTester;
use Tests\InteractsWithBeanstalkd;

/**
 * Class BeanstalkdQueueCest
 *
 * @package Tests\Integration\Driver
 */
class BeanstalkdQueueCest
{
    use InteractsWithBeanstalkd;

    /**
     * @param IntegrationTester $I
     */
    public function _before(IntegrationTester $I): void
    {
        $this->clearTube(BeanstalkdClientMother::default());
    }

    /**
     * @param IntegrationTester $I
     *
     * @throws \Initx\Exception\IllegalStateException
     * @env beanstalkd
     */
    public function add(IntegrationTester $I): void
    {
        $envelope = EnvelopeMother::any();
        $pheanstalk = BeanstalkdClientMother::default();
        $queue = new BeanstalkdQueue($pheanstalk, PheanstalkInterface::DEFAULT_TUBE);

        $actual = $queue->add($envelope);

        $this->seeQueueHasCurrentCount($I, $pheanstalk, 1);
        $this->seeCurrentJobIs($I, $pheanstalk, $envelope);
        $I->assertTrue($actual);
    }

    /**
     * @param IntegrationTester $I
     * @env beanstalkd
     */
    public function offer(IntegrationTester $I): void
    {
        $envelope = EnvelopeMother::any();
        $pheanstalk = BeanstalkdClientMother::default();
        $queue = new BeanstalkdQueue($pheanstalk, PheanstalkInterface::DEFAULT_TUBE);

        $queue->offer($envelope);

        $this->seeQueueHasCurrentCount($I, $pheanstalk, 1);
        $this->seeCurrentJobIs($I, $pheanstalk, $envelope);
    }

    /**
     * @param IntegrationTester $I
     *
     * @throws \Initx\Exception\IllegalStateException
     * @throws \Initx\Exception\NoSuchElementException
     * @env beanstalkd
     */
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
        $this->seeQueueHasCurrentCount($I, $pheanstalk, 0);
    }

    /**
     * @param IntegrationTester $I
     *
     * @throws \Initx\Exception\IllegalStateException
     * @env beanstalkd
     */
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
        $this->seeQueueHasCurrentCount($I, $pheanstalk, 0);
    }

    /**
     * @param IntegrationTester $I
     *
     * @throws \Initx\Exception\IllegalStateException
     * @env beanstalkd
     */
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
        $this->seeQueueHasCurrentCount($I, $pheanstalk, 2);
    }

    /**
     * @param IntegrationTester $I
     *
     * @throws \Initx\Exception\IllegalStateException
     * @throws \Initx\Exception\NoSuchElementException
     * @env beanstalkd
     */
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
        $this->seeQueueHasCurrentCount($I, $pheanstalk, 2);
    }
}
