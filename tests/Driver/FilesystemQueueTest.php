<?php declare(strict_types=1);

namespace Initx\Tests;

use Initx\Driver\FilesystemQueue;
use Initx\Exception\IllegalStateException;
use Initx\Tests\Double\EnvelopeMother;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;

class FilesystemQueueTest extends TestCase
{
    private const UNREACHABLE_PATH = '/some-unreachable-path-9191';

    /**
     * @var string
     */
    private $path;

    public function setUp(): void
    {
        $this->path = __DIR__ . '/../_output/' . Uuid::uuid4()->toString();
    }

    public function tearDown(): void
    {
        if (file_exists($this->path)) {
//            unlink($this->path);
        }
    }

    /**
     * @test
     */
    public function addOk(): void
    {
        // arrange
        $envelope = EnvelopeMother::any();
        $queue = new FilesystemQueue($this->path);

        // act
        $queue->add($envelope);

        // assert
        $this->assertFileExists($this->path);
    }

    /**
     * @test
     */
    public function addThrows(): void
    {
        // arrange
        $envelope = EnvelopeMother::any();
        $queue = new FilesystemQueue(self::UNREACHABLE_PATH);
        $this->expectException(IllegalStateException::class);

        // act
        $queue->add($envelope);
    }

    /**
     * @test
     */
    public function offerOk(): void
    {
        // arrange
        $envelope = EnvelopeMother::any();
        $queue = new FilesystemQueue($this->path);

        // act
        $queue->offer($envelope);

        // assert
        $this->assertFileExists($this->path);
    }

    /**
     * @test
     */
    public function offerFail(): void
    {
        // arrange
        $envelope = EnvelopeMother::any();
        $queue = new FilesystemQueue(self::UNREACHABLE_PATH);

        // act
        $queue->offer($envelope);

        // assert
        $this->assertFileNotExists($this->path);
    }
}
