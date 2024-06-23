<?php
/**
 * Autumn PHP Framework
 *
 * Date:        16/05/2024
 */

namespace Autumn\Lang;

use Autumn\Http\Message\Stream;
use Autumn\Interfaces\Renderable;
use Psr\Http\Message\StreamInterface;

class RenderableStream implements Renderable, StreamInterface, \Stringable
{
    private ?StreamInterface $target;
    private ?Renderable $renderable;

    public function __construct(Renderable $renderable, StreamInterface $target = null)
    {
        $this->renderable = $renderable;
        $this->target = $target;
    }

    /**
     * @return StreamInterface
     */
    public function getTarget(): StreamInterface
    {
        if (!$this->target) {
            $this->getContents();
        }

        return $this->target;
    }

    /**
     * @param StreamInterface|null $target
     */
    public function setTarget(?StreamInterface $target): void
    {
        $this->target = $target;
    }

    public function render(): void
    {
        $this->rewind();
        echo $this->getContents();
    }

    public function __toString(): string
    {
        return $this->getContents();
    }

    public function close(): void
    {
        $this->target?->close();
    }

    public function detach(): void
    {
        $this->target = null;
    }

    public function getSize(): ?int
    {
        return $this->getTarget()->getSize();
    }

    public function tell(): int
    {
        return $this->getTarget()->tell();
    }

    public function eof(): bool
    {
        return $this->getTarget()->eof();
    }

    public function isSeekable(): bool
    {
        return $this->getTarget()->isSeekable();
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        $this->getTarget()->seek($offset, $whence);
    }

    public function rewind(): void
    {
        $this->getTarget()?->rewind();
    }

    public function isWritable(): bool
    {
        return $this->getTarget()->isWritable();
    }

    public function write(string $string): void
    {
        $this->getTarget()->write($string);
    }

    public function isReadable(): bool
    {
        return $this->getTarget()->isReadable();
    }

    public function read(int $length): string
    {
        return $this->getTarget()->read($length);
    }

    public function getContents(): string
    {
        if ($this->target) {
            $this->target->rewind();
            return $this->target->getContents();
        }

        $this->target = Stream::temp();

        ob_start(function (string $chunk) {
            $this->target->write($chunk);
        });

        try {
            // Render the content
            $this->renderable->render();
            $this->target->rewind();
            return $this->target->getContents();
        } finally {
            ob_end_clean();
        }
    }

    public function getMetadata(?string $key = null): string
    {
        return $this->getTarget()->getMetadata($key);
    }
}