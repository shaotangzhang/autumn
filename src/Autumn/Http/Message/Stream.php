<?php
namespace Autumn\Http\Message;

use Autumn\Exceptions\ValidationException;
use Psr\Http\Message\StreamInterface;

class Stream implements StreamInterface, \Stringable
{
    public const DEFAULT_BUFFER_SIZE = 4096;

    private mixed $stream;
    private mixed $meta = null;

    public function __construct(mixed $resource, string $mode = 'r', array $options = null, array $params = null)
    {
        if (is_string($resource)) {
            if (isset($context)) {
                $context = stream_context_create($options, $params);
            } else {
                $context = null;
            }

            $resource = fopen($resource, $mode, false, $context);
        }

        if (get_resource_type($resource) !== 'stream') {
            throw new \InvalidArgumentException('Invalid stream resource.');
        }

        $this->stream = $resource;
    }

    public static function fromString(string $data): static
    {
        $stream = fopen('php://temp', 'w+');
        fwrite($stream, $data);
        rewind($stream);

        return new static($stream);
    }

    public static function temp(): static
    {
        static $g;
        return $g ??= new static('php://temp', 'rw+');
    }

    public static function input(): static
    {
        static $g;
        return $g ??= new static('php://input', 'r');
    }

    public static function output(): static
    {
        static $g;
        return $g ??= new static('php://output', 'r');
    }

    public static function stdin(): static
    {
        static $g;
        return $g ??= new static(STDIN);
    }

    public static function stdout(): static
    {
        static $g;
        return $g ??= new static(STDOUT);
    }

    public static function stderr(): static
    {
        static $g;
        return $g ??= new static(STDERR);
    }

    /**
     * Create an instance of a Stream, referring to a new created temporary file.
     *
     * @param string|null $directory
     * @param string|null $prefix
     * @param string $mode
     * @return false|static
     */
    public static function tempFile(string $directory = null, string $prefix = null, string $mode = 'w+'): false|static
    {
        $directory ??= sys_get_temp_dir();
        if ($tmpFile = tempnam($directory, $prefix)) {
            return new static($tmpFile, $mode);
        }
        return false;
    }

    public static function fromResource(mixed $value): static
    {
        if ($value instanceof static) {
            return $value;
        }

        if ($value instanceof self) {
            $instance = new static($value->stream, $value->getMetadata('mode'));
            $instance->meta = $value->meta;
            return $instance;
        }

        if (is_resource($value) && get_resource_type($value) === 'stream') {
            $meta = stream_get_meta_data($value);
            $instance = new static($value, $meta['mode'] ?? 'r');
            $instance->meta = $meta;
            return $instance;
        }

        throw ValidationException::of('Invalid Stream resource.');
    }

    public static function copy(mixed $source, mixed $target, int $bufferSize = null, int $length = null, int $offset = 0, int $bufferSizeForWrite = null): int
    {
        return static::fromResource($source)->copyTo($target, $bufferSize, $length, $offset, $bufferSizeForWrite);
    }

    public function copyTo(mixed $target, int $bufferSize = null, int $length = null, int $offset = 0, int $bufferSizeForWrite = null): int
    {
        $bufferSize ??= static::DEFAULT_BUFFER_SIZE;
        if ($bufferSize < 1) {
            throw ValidationException::of('Invalid buffer size for stream input.');
        }

        $bufferSizeForWrite ??= $bufferSize;
        if ($bufferSizeForWrite < 1) {
            throw ValidationException::of('Invalid buffer size for stream output.');
        }

        if (!$this->isReadable()) {
            throw ValidationException::of('The source stream is not readable.');
        }

        $resource = static::fromResource($target);
        if (!$resource->isWritable()) {
            throw ValidationException::of('The target stream is not writable.');
        }

        stream_set_read_buffer($resource->stream, $bufferSize);
        stream_set_write_buffer($this->stream, $bufferSizeForWrite);

        return stream_copy_to_stream($this->stream, $resource->stream, $length, $offset) ?: 0;
    }

    public function copyFrom(mixed $source, int $bufferSize = null, int $length = null, int $offset = 0, int $bufferSizeForWrite = null): int
    {
        $bufferSize ??= static::DEFAULT_BUFFER_SIZE;
        if ($bufferSize < 1) {
            throw ValidationException::of('Invalid buffer size for stream input.');
        }

        $bufferSizeForWrite ??= $bufferSize;
        if ($bufferSizeForWrite < 1) {
            throw ValidationException::of('Invalid buffer size for stream output.');
        }

        if (!$this->isWritable()) {
            throw ValidationException::of('The target stream is not writable.');
        }

        $resource = static::fromResource($source);
        if (!$resource->isWritable()) {
            throw ValidationException::of('The source stream is not readable.');
        }

        stream_set_read_buffer($this->stream, $bufferSize);
        stream_set_write_buffer($resource->stream, $bufferSizeForWrite);

        return stream_copy_to_stream($resource->stream, $this->stream, $length, $offset) ?: 0;
    }

    public function __toString(): string
    {
        return $this->getContents();
    }

    public function close(): void
    {
    }

    public function detach()
    {
        $resource = $this->stream;
        $this->stream = null;
        return $resource;
    }

    public function getSize(): ?int
    {
        $stats = fstat($this->stream);
        if ($stats !== false && isset($stats['size'])) {
            return $stats['size'];
        }
        return null;
    }

    public function tell(): int
    {
        $position = ftell($this->stream);
        if ($position !== false) {
            return $position;
        }
        throw new \RuntimeException('Failed to get stream position');
    }

    public function eof(): bool
    {
        return feof($this->stream);
    }

    public function isSeekable(): bool
    {
        return !!$this->getMetadata('seekable');
    }

    public function seek(int $offset, int $whence = SEEK_SET)
    {
        if (fseek($this->stream, $offset, $whence) !== 0) {
            throw new \RuntimeException('Failed to seek within the stream');
        }
    }

    public function rewind()
    {
        $this->seek(0);
    }

    public function isWritable(): bool
    {
        $meta = $this->getMetadata('mode');

        return $meta
            && (str_contains($meta, 'w') || str_contains($meta, 'a'));
    }

    public function write(string $string): int
    {
        $written = fwrite($this->stream, $string);

        if ($written === false) {
            throw new \RuntimeException('Failed to write to stream');
        }
        return $written;
    }

    public function isReadable(): bool
    {
        $meta = $this->getMetadata('mode');

        return $meta && str_contains($meta, 'r');
    }

    public function read(int $length): string
    {
        $data = fread($this->stream, $length);
        if ($data === false) {
            throw new \RuntimeException('Failed to read from stream');
        }
        return $data;
    }

    public function getContents(): string
    {
        $contents = stream_get_contents($this->stream);
        if ($contents === false) {
            throw new \RuntimeException('Failed to get stream contents');
        }

        return $contents;
    }

    public function getMetadata(?string $key = null): mixed
    {
        if ($this->meta === null) {
            $this->meta = stream_get_meta_data($this->stream);
        }

        if (!empty($key)) {
            return $this->meta[$key] ?? null;
        }

        return $this->meta;
    }
}