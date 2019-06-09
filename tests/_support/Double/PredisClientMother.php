<?php declare(strict_types=1);

namespace Initx\Querabilis\Tests\Double;

use Predis\Client;
use Predis\Connection\Parameters;

class PredisClientMother
{
    public static function default(): Client
    {
        $params = new Parameters([
                'host' => getenv('REDIS_HOST') ?: '127.0.0.1',
                'port' => getenv('REDIS_PORT') ?: 6379,
                'password' => getenv('REDIS_PASSWORD') ?: null,
        ]);

        return new Client($params);
    }
}
