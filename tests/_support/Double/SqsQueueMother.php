<?php declare(strict_types=1);

namespace Tests\Double;

use Aws\Sqs\Exception\SqsException;
use Faker\Factory;

class SqsQueueMother
{
    private const QUEUE_PREFIX = 'queue_mother_test_';

    public static function createQueue(): string
    {
        $client = SqsClientMother::default();
        $suffix = implode(
            '',
            Factory::create()->randomElements(['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'o', 'p'], 7)
        );
        $name = self::QUEUE_PREFIX . $suffix;

        $client->createQueue([
            'QueueName' => $name,
            'Attributes' => [
                'DelaySeconds' => 0,
                'VisibilityTimeout' => 0,
                // 4 KB
                'MaximumMessageSize' => 4096,
            ],
        ]);

        return $name;
    }

    public static function dropTestQueues(): void
    {
        $client = SqsClientMother::default();
        $result = $client->listQueues();

        foreach ($result->get('QueueUrls') as $queueUrl) {
            // check queue url for test prefix
            if (mb_strpos($queueUrl, self::QUEUE_PREFIX)) {
                try {
                    $client->deleteQueue([
                        'QueueUrl' => $queueUrl // REQUIRED
                    ]);
                } catch (SqsException $exception) {
                    // no need to log or rethrow Sqs exception in this case,
                    // probably aws returned not existing queue url (lag)
                }
            }
        }
    }
}
