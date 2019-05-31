<?php declare(strict_types=1);

namespace Initx\Driver;

use Initx\Envelope;
use Initx\Exception\IllegalStateException;
use Initx\Exception\NoSuchElementException;
use Initx\Queue;
use JMS\Serializer\SerializerInterface;
use Throwable;

final class FilesystemQueue implements Queue
{
    use HasFallbackSerializer;

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
        $this->serializer = $serializer;
        $this->path = $path;
        $this->fallbackSerializer();
    }

    public function add(Envelope $envelope): void
    {
        if (!$this->offer($envelope)) {
            throw IllegalStateException::create("Could not write to {$this->path}");
        }
    }

    public function offer(Envelope $envelope): bool
    {
        $content = $this->serializer->serialize($envelope, 'json').PHP_EOL;
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

    public function poll(): ?Envelope
    {
        $firstLine = $this->removeFirstLine();

        if (!$firstLine) {
            return null;
        }

        return $this->serializer->deserialize($firstLine, Envelope::class, 'json');
    }

    public function element(): Envelope
    {
        if (!$envelope = $this->peek()) {
            throw new NoSuchElementException('Queue empty');
        }

        return $envelope;
    }

    public function peek(): ?Envelope
    {
        $firstLine = $this->readFirstLine();

        if (!$firstLine) {
            return null;
        }

        return $this->serializer->deserialize($firstLine, Envelope::class, 'json');
    }
}
