<?php declare(strict_types=1);

namespace Tests\Unit\Driver;

use Initx\Querabilis\Driver\BeanstalkdQueue;
use Initx\Querabilis\Driver\HasFallbackSerializer;
use Mockery;
use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Job;
use Initx\Querabilis\Tests\Double\EnvelopeMother;
use Initx\Querabilis\Tests\IntegrationTester;

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

    public function add(IntegrationTester $I): void
    {
        // arrange
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

        // act
        $actual = $queue->add($envelope);

        // assert
        $I->assertTrue($actual);
    }

    /**
     * @param IntegrationTester $I
     */
    public function offer(IntegrationTester $I): void
    {
        // arrange
        $envelope = EnvelopeMother::any();

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

        // act
        $queue->offer($envelope);
    }

    public function remove(IntegrationTester $I): void
    {
        //arrange
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

        // act
        $actualOne = $queue->remove();
        $actualTwo = $queue->remove();

        // assert
        $I->assertEquals($envelopeOne, $actualOne);
        $I->assertEquals($envelopeTwo, $actualTwo);
    }

    public function poll(IntegrationTester $I)
    {
        // arrange
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

        // act
        $actualOne = $queue->poll();
        $actualTwo = $queue->poll();
        $actualThree = $queue->poll();

        // assert
        $I->assertEquals($envelopeOne, $actualOne);
        $I->assertEquals($envelopeTwo, $actualTwo);
        $I->assertNull($actualThree);
    }

    public function peek(IntegrationTester $I): void
    {
        // arrange
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

        // act
        $actualOne = $queue->peek();
        $actualTwo = $queue->peek();

        // assert
        $I->assertEquals($envelopeOne, $actualOne);
        // actual two = envelope one
        $I->assertEquals($envelopeOne, $actualTwo);
    }

    public function element(IntegrationTester $I): void
    {
        // arrange
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

        // act
        $actualOne = $queue->element();
        $actualTwo = $queue->element();

        // assert
        $I->assertEquals($envelopeOne, $actualOne);
        // actual two = envelope one
        $I->assertEquals($envelopeOne, $actualTwo);
    }
}
