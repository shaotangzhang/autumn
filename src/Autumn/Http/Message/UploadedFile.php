<?php

namespace Autumn\Http\Message;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

class UploadedFile implements UploadedFileInterface
{
    private bool $moved = false;
    private ?StreamInterface $stream = null;

    public function __construct(private readonly array $uploadedFileInfo = [])
    {
    }

    public static function fromStream(StreamInterface $stream, array $uploadedFileInfo = []): static
    {
        $uploadedFileInfo['size'] ??= $stream->getSize();
        $uploadedFileInfo['error'] ??= UPLOAD_ERR_OK;

        $file = $stream->getMetadata('uri');
        if (is_string($file)) {
            $uploadedFileInfo['name'] ??= basename(preg_split('/[#?]+/', $file)[0]);
            if (is_file($file)) {
                $uploadedFileInfo['tmp_name'] ??= $file;
            }
        }

        $instance = new static($uploadedFileInfo);
        $instance->stream = $stream;
        return $instance;
    }

    public function getStream(): StreamInterface
    {
        if ($this->moved) {
            throw new \RuntimeException('The uploaded file is already moved.');
        }

        if ($this->stream === null) {
            $file = $this->uploadedFileInfo['tmp_name'] ?? null;
            if (empty($file) || !is_uploaded_file($file)) {
                if (!str_starts_with(realpath($file), sys_get_temp_dir() . DIRECTORY_SEPARATOR)) {
                    throw new \RuntimeException('File is not a valid uploaded or temporary file');
                }
            }

            $this->stream = new Stream($file);
        }

        return $this->stream;
    }

    public function moveTo(string $targetPath): void
    {
        if ($this->moved) {
            throw new \RuntimeException('The uploaded file is already moved.');
        }

        if ($this->stream !== null) {
            $stream = $this->stream->detach();
            $this->stream = null;

            if (!is_resource($stream)) {
                throw new \RuntimeException('Invalid stream provided');
            }

            $target = fopen($targetPath, 'w+');
            if (!$target) {
                throw new \RuntimeException('Unable to create the target file.');
            }

            stream_copy_to_stream($stream, $target);
            fclose($target);
            fclose($stream);
        } else {
            $file = $this->uploadedFileInfo['tmp_name'] ?? null;
            if (empty($file)) {
                throw new \RuntimeException('No file available to move');
            }

            if (!is_uploaded_file($file)) {
                throw new \RuntimeException('File is not a valid uploaded file');
            }

            if (!move_uploaded_file($file, $targetPath)) {
                throw new \RuntimeException('Failed to move uploaded file');
            }
        }

        $this->moved = true;
    }

    public function getError(): int
    {
        return $this->uploadedFileInfo['error'];
    }

    public function getSize(): ?int
    {
        return $this->uploadedFileInfo['size'] ?? null;
    }

    public function getClientFilename(): ?string
    {
        return $this->uploadedFileInfo['name'] ?? null;
    }

    public function getClientMediaType(): ?string
    {
        return $this->uploadedFileInfo['type'] ?? null;
    }
}