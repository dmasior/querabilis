<?php declare(strict_types=1);

namespace Initx\Querabilis\Tests;

use Initx\Querabilis\Driver\HasFallbackSerializer;
use Initx\Querabilis\Envelope;
use Initx\Querabilis\Tests\_generated\IntegrationTesterActions;
use Pheanstalk\Contract\PheanstalkInterface;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
*/
class IntegrationTester extends \Codeception\Actor
{
    use IntegrationTesterActions;
    use HasFallbackSerializer;

   /**
    * Define custom actions here
    */

    /**
     * Clear the entire Beanstalk tube.
     *
     * @param PheanstalkInterface $pheanstalk
     */
    public function clearBeanstalkTube(PheanstalkInterface $pheanstalk)
    {
        while ($job = $pheanstalk->peekReady()) {
            $pheanstalk->delete($job);
        }
    }

    /**
     * Assert that the queue has a specific amount of ready messages.
     *
     * @param PheanstalkInterface $pheanstalk
     * @param int $count
     */
    public function seeBeanstalkQueueHasCurrentCount(PheanstalkInterface $pheanstalk, int $count)
    {
        $stats = $pheanstalk->statsTube(PheanstalkInterface::DEFAULT_TUBE);

        $this->assertEquals($count, (int)$stats['current-jobs-ready']);
    }

    /**
     * Assert what the current ready message is.
     *
     * @param PheanstalkInterface $pheanstalk
     * @param Envelope $envelope
     */
    public function seeBeanstalkCurrentEnvelope(PheanstalkInterface $pheanstalk, Envelope $envelope)
    {
        $serializer = $this->fallbackSerializer();
        $ready = $pheanstalk->peekReady();

        $serialized = $serializer->serialize($envelope, 'json');

        $this->assertEquals($serialized, $ready->getData());
    }
}
