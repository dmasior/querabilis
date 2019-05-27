<?php declare(strict_types=1);

namespace Initx\Tests\Double;

use Faker\Factory;
use Initx\Envelope;

class EnvelopeMother
{
    public static function any(): Envelope
    {
        $faker = Factory::create();

        return new Envelope(new Qtaz());
    }
}
class Qtaz {
    private $b = 123;
    protected $c = 344;
}
