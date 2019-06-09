<?php

namespace Tests;

use Initx\Driver\HasFallbackSerializer;
use Initx\Envelope;
use JMS\Serializer\SerializerInterface;
use Pheanstalk\Contract\PheanstalkInterface;
use Throwable;

trait InteractsWithBeanstalkd
{
    use HasFallbackSerializer;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct()
    {
        $this->serializer = $this->fallbackSerializer(null);
    }

    /**
     * Clear the entire tube.
     *
     * @param PheanstalkInterface $pheanstalk
     */
    private function clearTube(PheanstalkInterface $pheanstalk)
    {
        try {
            while ($job = $pheanstalk->peekReady()) {
                $pheanstalk->delete($job);
            }
        } catch (Throwable $e) {
        }
    }

    /**
     * Assert that the queue has a specific amount of ready messages.
     *
     * @param IntegrationTester   $I
     * @param PheanstalkInterface $pheanstalk
     * @param int                 $count
     */
    private function seeQueueHasCurrentCount(IntegrationTester $I, PheanstalkInterface $pheanstalk, int $count)
    {
        $stats = $pheanstalk->statsTube(PheanstalkInterface::DEFAULT_TUBE);

        $I->assertEquals($count, (int)$stats['current-jobs-ready']);
    }

    /**
     * Assert what the current ready message is.
     *
     * @param IntegrationTester   $I
     * @param PheanstalkInterface $pheanstalk
     * @param Envelope            $envelope
     */
    private function seeCurrentJobIs(IntegrationTester $I, PheanstalkInterface $pheanstalk, Envelope $envelope)
    {
        $ready = $pheanstalk->peekReady();

        $serialized = $this->serializer->serialize($envelope, 'json');

        $I->assertEquals($serialized, $ready->getData());
    }
}
