<?php

namespace Autumn\Mailing;

use Autumn\Exceptions\NotFoundException;
use Autumn\Exceptions\ValidationException;
use Autumn\Lang\MimeType;

class Attachment implements AttachmentInterface
{
    private string $filePath;
    private ?string $fileName = null;
    private ?string $mimeType = null;
    private ?int $fileSize = null;
    private ?string $content = null;

    public function __construct(string $filePath, string $mimeType = null, string $fileName = null)
    {
        $this->setFilePath($filePath);
        if ($fileName) {
            $this->setFileName($fileName);
        }
        if ($mimeType) {
            $this->setMimeType($mimeType);
        }
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function setFilePath(string $filePath): void
    {
        if (!is_file($filePath)) {
            throw NotFoundException::of('File `%s` is not found.');
        }

        $this->filePath = $filePath;
    }

    public function getFileName(): string
    {
        return $this->fileName ??= basename($this->filePath);
    }

    public function setFileName(string $fileName): void
    {
        $this->fileName = $fileName;
    }

    public function getMimeType(): string
    {
        return $this->mimeType ?? MimeType::detect($this->filePath);
    }

    public function setMimeType(string $mimeType): void
    {
        $this->mimeType = $mimeType;
    }

    public function getFileSize(): int
    {
        return $this->fileSize ??= filesize($this->filePath);
    }

    public function setFileSize(int $fileSize): void
    {
        $this->fileSize = $fileSize;
    }

    public function getContent(): string
    {
        return $this->content ??= file_get_contents($this->filePath);
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }
}