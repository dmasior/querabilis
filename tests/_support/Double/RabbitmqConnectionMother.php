<?php declare(strict_types=1);

namespace Initx\Querabilis\Tests\Double;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitmqConnectionMother
{
    public static function default(): AMQPStreamConnection
    {
        return new AMQPStreamConnection(
            '127.0.0.1',
            5672,
            'guest',
            'guest'
        );
    }
}
