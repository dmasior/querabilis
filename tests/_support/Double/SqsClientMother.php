<?php declare(strict_types=1);

namespace Tests\Double;

use Aws\Sqs\SqsClient;

class SqsClientMother
{
    public static function default(): SqsClient
    {
        return new SqsClient([
            'profile' => 'default',
            'region' => 'eu-west-1',
            'version' => '2012-11-05'
        ]);
    }
}
