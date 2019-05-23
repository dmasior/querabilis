<?php declare(strict_types=1);

namespace Initx\Exception;

use Exception;

class IllegalStateException extends Exception
{
    public static function create(string $message)
    {
        return new static($message);
    }
}
