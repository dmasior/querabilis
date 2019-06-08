<?php declare(strict_types=1);

namespace Tests\Unit\Driver;

use Initx\Driver\BeanstalkdQueue;
use Initx\Driver\HasFallbackSerializer;
use Mockery;
use Mockery\Mock;
use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Job;
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
    use HasFallbackSerializer;

    /**
     * @var \JMS\Serializer\SerializerInterface
     */
    private $serializer;

    public function __construct()
    {
        $this->serializer = $this->fallbackSerializer();
    }

    /**
     * @param IntegrationTester $I
     *
     * @throws \Initx\Exception\IllegalStateException
     */
    public function add(IntegrationTester $I): void
    {
        $envelope = EnvelopeMother::any();
        $serialized = $this->serializer->serialize($envelope, 'json');

        $pheanstalk = Mockery::mock(PheanstalkInterface::class);
        $pheanstalk->expects()
                   ->useTube(PheanstalkInterface::DEFAULT_TUBE)
                   ->once()
                   ->andReturnSelf();

        $pheanstalk->expects()
            ->put($serialized)
            ->once()
            ->andReturn(Mockery::mock(Job::class));

        $queue = new BeanstalkdQueue($pheanstalk, PheanstalkInterface::DEFAULT_TUBE);


        $actual = $queue->add($envelope);

        $I->assertTrue($actual);
    }

    /**
     * @param IntegrationTester $I
     */
    public function offer(IntegrationTester $I): void
    {
        $envelope = EnvelopeMother::any();
        $serialized = $this->serializer->serialize($envelope, 'json');

        $pheanstalk = Mockery::mock(PheanstalkInterface::class);
        $pheanstalk->expects()
                   ->useTube(PheanstalkInterface::DEFAULT_TUBE)
                   ->once()
                   ->andReturnSelf();

        $pheanstalk->expects()
                   ->put(Mockery::any())
                   ->once()
                   ->andReturn(Mockery::mock(Job::class));

        $queue = new BeanstalkdQueue($pheanstalk, PheanstalkInterface::DEFAULT_TUBE);

        $envelope = EnvelopeMother::any();

        $queue->offer($envelope);
    }

    /**
     * @param IntegrationTester $I
     *
     * @throws \Initx\Exception\IllegalStateException
     * @throws \Initx\Exception\NoSuchElementException
     */
    public function remove(IntegrationTester $I): void
    {
        $envelopeOne = EnvelopeMother::any();
        $envelopeTwo = EnvelopeMother::any();

        $serializedOne = $this->serializer->serialize($envelopeOne, 'json');
        $serializedTwo = $this->serializer->serialize($envelopeTwo, 'json');

        $pheanstalk = Mockery::mock(PheanstalkInterface::class);
        $pheanstalk->expects()
                   ->useTube(PheanstalkInterface::DEFAULT_TUBE)
                   ->twice()
                   ->andReturnSelf();

        $pheanstalk->expects()
                   ->watch(PheanstalkInterface::DEFAULT_TUBE)
                   ->twice()
                   ->andReturnSelf();

        $pheanstalk->shouldReceive('reserveWithTimeout')
                   ->with(0)
                   ->twice()
                   ->andReturn(
                       Mockery::mock(Job::class)
                              ->shouldReceive('getData')
                              ->andReturn($serializedOne)
                              ->getMock(),
                       Mockery::mock(Job::class)
                              ->shouldReceive('getData')
                              ->andReturn($serializedTwo)
                              ->getMock()
                   );

        $pheanstalk->shouldReceive('put')
                   ->with(Mockery::any())
                   ->twice()
                   ->andReturn(Mockery::mock(Job::class));

        $queue = new BeanstalkdQueue($pheanstalk, PheanstalkInterface::DEFAULT_TUBE);

        $queue->add($envelopeOne);
        $queue->add($envelopeTwo);

        $actualOne = $queue->remove();
        $actualTwo = $queue->remove();

        $I->assertEquals($envelopeOne, $actualOne);
        $I->assertEquals($envelopeTwo, $actualTwo);
    }

    public function poll(IntegrationTester $I)
    {
        $envelopeOne = EnvelopeMother::any();
        $envelopeTwo = EnvelopeMother::any();

        $serializedOne = $this->serializer->serialize($envelopeOne, 'json');
        $serializedTwo = $this->serializer->serialize($envelopeTwo, 'json');

        $pheanstalk = Mockery::mock(PheanstalkInterface::class);
        $pheanstalk->expects()
                   ->useTube(PheanstalkInterface::DEFAULT_TUBE)
                   ->times(3)
                   ->andReturnSelf();

        $pheanstalk->expects()
                   ->watch(PheanstalkInterface::DEFAULT_TUBE)
                   ->times(3)
                   ->andReturnSelf();

        $pheanstalk->shouldReceive('reserveWithTimeout')
                   ->with(0)
                   ->twice()
                   ->andReturn(
                       Mockery::mock(Job::class)
                              ->shouldReceive('getData')
                              ->andReturn($serializedOne)
                              ->getMock(),
                       Mockery::mock(Job::class)
                              ->shouldReceive('getData')
                              ->andReturn($serializedTwo)
                              ->getMock(),
                       null
                   );

        $pheanstalk->shouldReceive('put')
                   ->with(Mockery::any())
                   ->times(3)
                   ->andReturn(Mockery::mock(Job::class));

        $queue = new BeanstalkdQueue($pheanstalk, PheanstalkInterface::DEFAULT_TUBE);

        $queue->add($envelopeOne);
        $queue->add($envelopeTwo);

        $actualOne = $queue->poll();
        $actualTwo = $queue->poll();
        $actualThree = $queue->poll();

        $I->assertEquals($envelopeOne, $actualOne);
        $I->assertEquals($envelopeTwo, $actualTwo);
        $I->assertNull($actualThree);
    }

    /**
     * @param IntegrationTester $I
     *
     * @throws \Initx\Exception\IllegalStateException
     */
    public function peek(IntegrationTester $I): void
    {
        $envelopeOne = EnvelopeMother::any();
        $envelopeTwo = EnvelopeMother::any();

        $serializedOne = $this->serializer->serialize($envelopeOne, 'json');

        $pheanstalk = Mockery::mock(PheanstalkInterface::class);
        $pheanstalk->expects()
                   ->useTube(PheanstalkInterface::DEFAULT_TUBE)
                   ->twice()
                   ->andReturnSelf();

        $pheanstalk->expects()
                   ->watch(PheanstalkInterface::DEFAULT_TUBE)
                   ->twice()
                   ->andReturnSelf();

        $pheanstalk->shouldReceive('peekReady')
                   ->twice()
                   ->andReturn(
                       Mockery::mock(Job::class)
                              ->shouldReceive('getData')
                              ->andReturn($serializedOne)
                              ->getMock(),
                       Mockery::mock(Job::class)
                              ->shouldReceive('getData')
                              ->andReturn($serializedOne)
                              ->getMock()
                   );

        $pheanstalk->shouldReceive('put')
                   ->with(Mockery::any())
                   ->twice()
                   ->andReturn(Mockery::mock(Job::class));

        $queue = new BeanstalkdQueue($pheanstalk, PheanstalkInterface::DEFAULT_TUBE);

        $queue->add($envelopeOne);
        $queue->add($envelopeTwo);

        $actualOne = $queue->peek();
        $actualTwo = $queue->peek();

        $I->assertEquals($envelopeOne, $actualOne);
        // actual two = envelope one
        $I->assertEquals($envelopeOne, $actualTwo);
    }

    /**
     * @param IntegrationTester $I
     *
     * @throws \Initx\Exception\IllegalStateException
     * @throws \Initx\Exception\NoSuchElementException
     */
    public function element(IntegrationTester $I): void
    {
        $envelopeOne = EnvelopeMother::any();
        $envelopeTwo = EnvelopeMother::any();

        $serializedOne = $this->serializer->serialize($envelopeOne, 'json');

        $pheanstalk = Mockery::mock(PheanstalkInterface::class);
        $pheanstalk->expects()
                   ->useTube(PheanstalkInterface::DEFAULT_TUBE)
                   ->twice()
                   ->andReturnSelf();

        $pheanstalk->expects()
                   ->watch(PheanstalkInterface::DEFAULT_TUBE)
                   ->twice()
                   ->andReturnSelf();

        $pheanstalk->shouldReceive('peekReady')
                   ->twice()
                   ->andReturn(
                       Mockery::mock(Job::class)
                              ->shouldReceive('getData')
                              ->andReturn($serializedOne)
                              ->getMock(),
                       Mockery::mock(Job::class)
                              ->shouldReceive('getData')
                              ->andReturn($serializedOne)
                              ->getMock()
                   );

        $pheanstalk->shouldReceive('put')
                   ->with(Mockery::any())
                   ->twice()
                   ->andReturn(Mockery::mock(Job::class));

        $queue = new BeanstalkdQueue($pheanstalk, PheanstalkInterface::DEFAULT_TUBE);

        $queue->add($envelopeOne);
        $queue->add($envelopeTwo);

        $actualOne = $queue->element();
        $actualTwo = $queue->element();

        $I->assertEquals($envelopeOne, $actualOne);
        // actual two = envelope one
        $I->assertEquals($envelopeOne, $actualTwo);
    }
}
