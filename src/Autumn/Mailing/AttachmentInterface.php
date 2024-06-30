<?php

namespace Autumn\Mailing;

interface AttachmentInterface
{
    /**
     * Get the file path of the attachment.
     *
     * @return string
     */
    public function getFilePath(): string;

    /**
     * Get the file name of the attachment.
     *
     * @return string
     */
    public function getFileName(): string;

    /**
     * Get the MIME type of the attachment.
     *
     * @return string
     */
    public function getMimeType(): string;

    /**
     * Get the size of the attachment in bytes.
     *
     * @return int
     */
    public function getFileSize(): int;

    /**
     * Get the content of the attachment.
     *
     * @return string
     */
    public function getContent(): string;
}
