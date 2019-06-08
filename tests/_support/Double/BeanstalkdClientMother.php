<?php declare(strict_types=1);

namespace Tests\Double;

use Pheanstalk\Contract\PheanstalkInterface;
use Pheanstalk\Pheanstalk;

class BeanstalkdClientMother
{
    public static function default(): PheanstalkInterface
    {
        return Pheanstalk::create(
            (string)getenv('BEANSTALKD_HOST') ?: '127.0.0.1',
            (int)getenv('BEANSTALKD_PORT') ?: PheanstalkInterface::DEFAULT_PORT,
            (int)getenv('BEANSTALKD_TIMEOUT') ?: 10
        );
    }
}
