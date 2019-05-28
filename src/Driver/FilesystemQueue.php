<?php declare(strict_types=1);

namespace Initx\Driver;

use Initx\Envelope;
use Initx\Exception\IllegalStateException;
use Initx\Exception\NoSuchElementException;
use Initx\Queue;
use JMS\Serializer\SerializerBuilder;
use JMS\Serializer\SerializerInterface;
use Throwable;

final class FilesystemQueue implements Queue
{
    /**
     * @var string
     */
    private $path;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(string $path, SerializerInterface $serializer = null)
    {
        if (!$serializer) {
            $separator = DIRECTORY_SEPARATOR;
            $metaDir = sprintf('%s%s..%s..%sconfig%sjms', __DIR__, $separator, $separator, $separator, $separator);
            $serializer = SerializerBuilder::create()
                ->addMetadataDir($metaDir, 'Initx')
                ->build();
        }
        $this->serializer = $serializer;
        $this->path = $path;
    }

    /**
     * Inserts an element if possible, otherwise throwing exception.
     *
     * @param Envelope $envelope
     * @return void
     * @throws IllegalStateException
     */
    public function add(Envelope $envelope): void
    {
        if (!$this->write($envelope)) {
            throw IllegalStateException::create("Could not write to {$this->path}");
        }
    }

    /**
     * Inserts an element if possible.
     *
     * @param Envelope $envelope
     * @return void
     */
    public function offer(Envelope $envelope): void
    {
        $this->write($envelope);
    }

    private function write(Envelope $envelope): bool
    {
        $content = $this->serializer->serialize($envelope, 'json') . PHP_EOL;
        try {
            $result = (bool)file_put_contents(
                $this->path,
                $content,
                FILE_APPEND
            );
        } catch (Throwable $exception) {
            $result = false;
        }

        return $result;
    }

    /**
     * Remove and return head of queue, otherwise throwing exception.
     *
     * @return Envelope
     * @throws NoSuchElementException | IllegalStateException
     */
    public function remove(): Envelope
    {
        if (!$envelope = $this->poll()) {
            throw new NoSuchElementException('Queue empty');
        }

        return $envelope;
    }

    private function removeFirstLine(): ?string
    {
        if (!file_exists($this->path)) {
            throw new IllegalStateException("File $this->path not exists");
        }
        $firstLine = null;
        if ($handle = fopen($this->path, 'cb+')) {
            if (!flock($handle, LOCK_EX)) {
                fclose($handle);
            }
            $offset = 0;
            $len = filesize($this->path);
            while (($line = fgets($handle, 4096)) !== false) {
                if (!$firstLine) {
                    $firstLine = $line;
                    $offset = strlen($firstLine);
                    continue;
                }
                $pos = ftell($handle);
                fseek($handle, $pos - strlen($line) - $offset);
                fwrite($handle, $line);
                fseek($handle, $pos);
            }
            fflush($handle);
            ftruncate($handle, $len - $offset);
            flock($handle, LOCK_UN);
            fclose($handle);
        }

        return $firstLine;
    }

    private function readFirstLine(): ?string
    {
        if (!file_exists($this->path)) {
            throw new IllegalStateException("File $this->path not exists");
        }

        $firstLine = fgets(fopen($this->path, 'rb'));

        return $firstLine ?: null;
    }

    /**
     * Remove and return head of queue, otherwise returning null.
     *
     * @return Envelope | null
     * @throws IllegalStateException
     */
    public function poll(): ?Envelope
    {
        $firstLine = $this->removeFirstLine();

        if (!$firstLine) {
            return null;
        }

        return $this->serializer->deserialize($firstLine, Envelope::class, 'json');
    }

    /**
     * Return but do not remove head of queue, otherwise throwing exception.
     *
     * @return Envelope
     * @throws NoSuchElementException | IllegalStateException
     */
    public function element(): Envelope
    {
        if (!$envelope = $this->peek()) {
            throw new NoSuchElementException('Queue empty');
        }

        return $envelope;
    }

    /**
     * Return but do not remove head of queue, otherwise returning null.
     *
     * @return Envelope | null
     * @throws IllegalStateException
     */
    public function peek(): ?Envelope
    {
        $firstLine = $this->readFirstLine();

        if (!$firstLine) {
            return null;
        }

        return $this->serializer->deserialize($firstLine, Envelope::class, 'json');
    }
}
