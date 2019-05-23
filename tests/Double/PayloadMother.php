<?php declare(strict_types=1);

namespace Initx\Tests\Double;

use Faker\Factory;
use Initx\Payload;
use Initx\PlainPayload;

class PayloadMother
{
    public static function any(): Payload
    {
        $faker = Factory::create();

        return new PlainPayload($faker->text);
    }
}
