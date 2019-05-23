<?php declare(strict_types=1);

namespace Initx\Tests;

use Initx\Driver\FilesystemQueue;
use Initx\Tests\Double\EnvelopeMother;
use PHPUnit\Framework\TestCase;

class FilesystemQueueTest extends TestCase
{
    /**
     * @var string
     */
    private $path;

    public function setUp(): void
    {
        $this->path = __DIR__ . '/../_output/' . __CLASS__ . microtime();
    }

    public function tearDown(): void
    {
        unlink($this->path);
    }

    /**
     * @test
     */
    public function add()
    {
        // arrange
        $envelope = EnvelopeMother::any();
        $queue = new FilesystemQueue($this->path);

        // act
        $queue->add($envelope);

        // assert
        $this->assertTrue(true);
    }

    public function offer()
    {
    }
}
