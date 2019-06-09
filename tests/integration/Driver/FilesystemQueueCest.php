<?php declare(strict_types=1);

namespace Initx\Querabilis\Tests\Integration\Driver;

use Initx\Querabilis\Driver\FilesystemQueue;
use Initx\Querabilis\Exception\IllegalStateException;
use Initx\Querabilis\Exception\NoSuchElementException;
use Ramsey\Uuid\Uuid;
use Initx\Querabilis\Tests\Double\EnvelopeMother;
use Initx\Querabilis\Tests\IntegrationTester;

class FilesystemQueueCest
{
    private const UNREACHABLE_PATH = '/some-unreachable-path-9191';

    /**
     * @var string
     */
    private $path;

    public function _before(): void
    {
        $this->path = codecept_data_dir(Uuid::uuid4()->toString());
    }

    public function _after(): void
    {
        if (file_exists($this->path)) {
            unlink($this->path);
        }
    }

    public function addOk(IntegrationTester $I): void
    {
        // arrange
        $envelope = EnvelopeMother::any();
        $queue = new FilesystemQueue($this->path);

        // act
        $actual = $queue->add($envelope);

        // assert
        $I->assertTrue($actual);
    }

    public function addThrows(IntegrationTester $I): void
    {
        // arrange
        $envelope = EnvelopeMother::any();
        $queue = new FilesystemQueue(self::UNREACHABLE_PATH);

        // act
        $I->expectException(IllegalStateException::class, function () use ($queue, $envelope) {
            $queue->add($envelope);
        });
    }

    public function offerOk(IntegrationTester $I): void
    {
        // arrange
        $envelope = EnvelopeMother::any();
        $queue = new FilesystemQueue($this->path);

        // act
        $queue->offer($envelope);

        // assert
        $I->assertFileExists($this->path);
    }

    public function offerFail(IntegrationTester $I): void
    {
        // arrange
        $envelope = EnvelopeMother::any();
        $queue = new FilesystemQueue(self::UNREACHABLE_PATH);

        // act
        $I->expectException(IllegalStateException::class, function () use ($queue, $envelope) {
            $queue->offer($envelope);
        });
    }

    public function removeOk(IntegrationTester $I): void
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
        $I->assertEquals($actualOne, $one);
        $I->assertEquals($actualTwo, $two);
    }

    public function removeThrowsEmptyQueueException(IntegrationTester $I): void
    {
        // arrange
        touch($this->path);
        $queue = new FilesystemQueue($this->path);

        // act
        $I->expectException(NoSuchElementException::class, function () use ($queue) {
            $queue->remove();
        });
    }

    public function removeThrowsIllegalStateWhenFileNotExists(IntegrationTester $I): void
    {
        // arrange
        $queue = new FilesystemQueue($this->path);

        // act
        $I->expectException(IllegalStateException::class, function () use ($queue) {
            $queue->remove();
        });
    }

    public function elementOk(IntegrationTester $I): void
    {
        // arrange
        $one = EnvelopeMother::any();
        $queue = new FilesystemQueue($this->path);
        $queue->add($one);

        // act
        $actualOne = $queue->element();
        $actualTwo = $queue->element();

        // assert
        $I->assertEquals($actualOne, $one);
        $I->assertEquals($actualTwo, $one);
    }

    public function peekOk(IntegrationTester $I): void
    {
        // arrange
        $one = EnvelopeMother::any();
        $queue = new FilesystemQueue($this->path);
        $queue->add($one);

        // act
        $actualOne = $queue->peek();
        $actualTwo = $queue->peek();

        // assert
        $I->assertEquals($actualOne, $one);
        $I->assertEquals($actualTwo, $one);
    }

    public function peekNull(IntegrationTester $I): void
    {
        // arrange
        touch($this->path);
        $queue = new FilesystemQueue($this->path);

        // act
        $actualOne = $queue->peek();
        $actualTwo = $queue->peek();

        // assert
        $I->assertNull($actualOne);
        $I->assertNull($actualTwo);
    }

    public function elementThrowsQueueEmpty(IntegrationTester $I): void
    {
        // arrange
        touch($this->path);
        $queue = new FilesystemQueue($this->path);

        // act
        $I->expectException(NoSuchElementException::class, function () use ($queue) {
            $queue->element();
        });
    }
}
