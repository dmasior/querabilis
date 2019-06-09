<?php declare(strict_types=1);

namespace Initx\Querabilis\Tests\Double;

use Faker\Factory;
use Initx\Querabilis\Envelope;

class EnvelopeMother
{
    public static function any(): Envelope
    {
        $faker = Factory::create();

        return new Envelope($faker->text, $faker->company, $faker->dateTime);
    }
}
