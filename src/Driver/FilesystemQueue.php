<?php declare(strict_types=1);

namespace Initx\Querabilis\Driver;

use Initx\Querabilis\Envelope;
use Initx\Querabilis\Exception\IllegalStateException;
use Initx\Querabilis\Exception\NoSuchElementException;
use Initx\Querabilis\Queue;
use JMS\Serializer\SerializerInterface;

final class FilesystemQueue implements Queue
{
    use HasFallbackSerializer;
    use HasDefaultRemoveAndElement;

    /**
     * @var string
     */
    private $path;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    public function __construct(string $path, ?SerializerInterface $serializer = null)
    {
        $this->serializer = $this->fallbackSerializer($serializer);
        $this->path = $path;
    }

    public function add(Envelope $envelope): bool
    {
        if (!$this->offer($envelope)) {
            $this->throwItIsNotWriteable();
        }

        return true;
    }

    public function offer(Envelope $envelope): bool
    {
        $content = $this->serializer->serialize($envelope, 'json').PHP_EOL;

        $result = (bool)@file_put_contents(
            $this->path,
            $content,
            FILE_APPEND
        );

        if (!$result) {
            $this->throwItIsNotWriteable();
        }

        return true;
    }

    public function poll(): ?Envelope
    {
        $firstLine = $this->removeFirstLine();

        if (!$firstLine) {
            return null;
        }

        return $this->serializer->deserialize($firstLine, Envelope::class, 'json');
    }

    public function peek(): ?Envelope
    {
        $firstLine = $this->readFirstLine();

        if (!$firstLine) {
            return null;
        }

        return $this->serializer->deserialize($firstLine, Envelope::class, 'json');
    }

    private function removeFirstLine(): ?string
    {
        if (!file_exists($this->path)) {
            $this->throwItNotExists();
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
            $this->throwItNotExists();
        }

        $firstLine = fgets(fopen($this->path, 'rb'));

        return $firstLine ?: null;
    }

    private function throwItIsNotWriteable(): void
    {
        throw new IllegalStateException("Could not write to file: {$this->path}");
    }

    private function throwItNotExists(): void
    {
        throw new IllegalStateException("File $this->path not exists");
    }
}
