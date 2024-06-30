<?php

namespace Autumn\Mailing;

use Autumn\Http\Message\HeadersTrait;

class Mail implements MailInterface
{
    public const TO = RecipientTypeEnum::TO;
    public const CC = RecipientTypeEnum::CC;
    public const BCC = RecipientTypeEnum::BCC;

    public const ENCRYPTION_NONE = 'None';
    public const ENCRYPTION_TLS = 'TLS';
    public const ENCRYPTION_SSL = 'SSL';
    public const ENCRYPTION_STARTTLS = 'STARTTLS';

    public const FORMAT_TEXT = 'TEXT';
    public const FORMAT_HTML = 'HTML';

    public const DEFAULT_ENCODING = 'utf-8';

    use HeadersTrait;

    private string $subject = '';
    private string $encryption = self::ENCRYPTION_NONE;
    private string $format = self::FORMAT_TEXT;
    private string $encoding = self::DEFAULT_ENCODING;
    private string $body = '';

    /**
     * @var RecipientInterface[]
     */
    private array $recipients = [];

    /**
     * @var AttachmentInterface[]
     */
    private array $attachments = [];

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getRecipients(): array
    {
        return $this->recipients;
    }

    public function getEncryption(): string
    {
        return $this->encryption;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getFormat(): string
    {
        return $this->format;
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    public function setEncoding(string $encoding): void
    {
        $this->encoding = $encoding;
    }

    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }

    public function setRecipients(array $recipients): void
    {
        $this->recipients = $recipients;
    }

    public function setEncryption(string $encryption): void
    {
        $this->encryption = $encryption;
    }

    public function setBody(string $body): void
    {
        $this->body = $body;
    }

    public function addRecipient(RecipientInterface $recipient): void
    {
        $this->recipients[] = $recipient;
    }

    public function to(string $email, string $name = null): static
    {
        $new = new Recipient(RecipientTypeEnum::TO, $email, $name);

        foreach ($this->recipients as $index => $recipient) {
            if ($recipient->getType() === RecipientTypeEnum::TO) {
                $this->recipients[$index] = $new;
                return $new;
            }
        }

        $this->addRecipient($new);
        return $this;
    }

    public function subject(string $subject): static
    {
        $this->subject = $subject;
        return $this;
    }

    public function body(string $body): static
    {
        $this->body = $body;
        return $this;
    }

    public function cc(string $email, string $name = null): static
    {
        $new = new Recipient(RecipientTypeEnum::CC, $email, $name);
        $this->addRecipient($new);
        return $this;
    }

    public function bcc(string $email, string $name = null): static
    {
        $new = new Recipient(RecipientTypeEnum::BCC, $email, $name);
        $this->addRecipient($new);
        return $this;
    }

    public function attach(string $filePath, string $mimeType = null): static
    {
        $attachment = new Attachment($filePath, $mimeType);
        $this->addAttachment($attachment);
        return $this;
    }

    public function getAttachments(): array
    {
        return $this->attachments;
    }

    public function addAttachment(AttachmentInterface $attachment): void
    {
        $this->attachments[] = $attachment;
    }
}
