<?php declare(strict_types=1);

namespace Initx\Tests;

use Initx\Driver\FilesystemQueue;
use Initx\Exception\IllegalStateException;
use Initx\Exception\NoSuchElementException;
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
            unlink($this->path);
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

    /**
     * @test
     */
    public function removeOk(): void
    {
        // arrange
        $one = EnvelopeMother::any();
        $two = EnvelopeMother::any();
        $queue = new FilesystemQueue($this->path);
        $queue->add($one);
        $queue->add($two);

        // act
        $actualOne = $queue->remove();
        $actualTwo = $queue->remove();

        // assert
        $this->assertEquals($actualOne, $one);
        $this->assertEquals($actualTwo, $two);
    }

    /**
     * @test
     */
    public function removeThrowsEmptyQueueException(): void
    {
        // arrange
        touch($this->path);
        $queue = new FilesystemQueue($this->path);
        $this->expectException(NoSuchElementException::class);

        // act
        $queue->remove();
    }

    /**
     * @test
     */
    public function removeThrowsIllegalStateWhenFileNotExists(): void
    {
        // arrange
        $queue = new FilesystemQueue($this->path);
        $this->expectException(IllegalStateException::class);

        // act
        $queue->remove();
    }

    /**
     * @test
     */
    public function elementOk(): void
    {
        // arrange
        $one = EnvelopeMother::any();
        $queue = new FilesystemQueue($this->path);
        $queue->add($one);

        // act
        $actualOne = $queue->element();
        $actualTwo = $queue->element();

        // assert
        $this->assertEquals($actualOne, $one);
        $this->assertEquals($actualTwo, $one);
    }

    /**
     * @test
     */
    public function peekOk(): void
    {
        // arrange
        $one = EnvelopeMother::any();
        $queue = new FilesystemQueue($this->path);
        $queue->add($one);

        // act
        $actualOne = $queue->peek();
        $actualTwo = $queue->peek();

        // assert
        $this->assertEquals($actualOne, $one);
        $this->assertEquals($actualTwo, $one);
    }

    /**
     * @test
     */
    public function peekNull(): void
    {
        // arrange
        touch($this->path);
        $queue = new FilesystemQueue($this->path);

        // act
        $actualOne = $queue->peek();
        $actualTwo = $queue->peek();

        // assert
        $this->assertNull($actualOne);
        $this->assertNull($actualTwo);
    }

    /**
     * @test
     */
    public function elementThrowsQueueEmpty(): void
    {
        // arrange
        touch($this->path);
        $queue = new FilesystemQueue($this->path);
        $this->expectException(NoSuchElementException::class);

        // act
        $queue->element();
    }
}
