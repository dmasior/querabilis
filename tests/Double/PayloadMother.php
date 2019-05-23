<?php declare(strict_types=1);

namespace Initx\Tests\Double;

use Faker\Factory;
use Initx\Payload;

class PayloadMother
{
    public static function any(): Payload
    {
        $faker = Factory::create();

        return new PlainPayload($faker->text);
    }
}
