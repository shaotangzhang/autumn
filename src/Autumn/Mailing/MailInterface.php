<?php

namespace Autumn\Mailing;

/**
 * Interface MailInterface
 *
 * Represents a mail message with subject, recipients, and encryption details.
 */
interface MailInterface
{
    /**
     * Get the subject of the mail.
     *
     * @return string
     */
    public function getSubject(): string;

    /**
     * Get the recipients of the mail.
     *
     * @return RecipientInterface[]
     */
    public function getRecipients(): array;

    /**
     * Get the encryption type of the mail.
     *
     * @return string
     */
    public function getEncryption(): string;

    /**
     * Get the format type of the mail.
     *
     * @return string
     */
    public function getFormat(): string;

    /**
     * Get the encoding of the mail.
     *
     * @return string
     */
    public function getEncoding(): string;

    /**
     * Get the body of the mail.
     *
     * @return string
     */
    public function getBody(): string;

    /**
     * Get the headers of the mail.
     *
     * @return array
     */
    public function getHeaders(): array;

    /**
     * Get the attachments of the mail
     * @return AttachmentInterface[]
     */
    public function getAttachments(): array;
}
