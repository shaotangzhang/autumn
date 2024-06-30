<?php

namespace Autumn\Mailing;

class DefaultMailer implements MailerInterface
{
    private ?string $senderEmail = null;

    public function getSenderEmail(): string
    {
        return $this->senderEmail ??= env('MAIL_SENDER_EMAIL', '');
    }

    /**
     * @param string|null $senderEmail
     */
    public function setSenderEmail(?string $senderEmail): void
    {
        $this->senderEmail = $senderEmail;
    }

    public function send(MailInterface $mail, array $context = null): bool
    {
        $to = $this->prepareRecipients($mail->getRecipients());
        $subject = $mail->getSubject();
        $message = $mail->getBody();
        $headers = $this->prepareHeaders($mail);

        // Send email using PHP's mail() function
        return mail($to, $subject, $message, $headers);
    }

    protected function prepareRecipients(array $recipients): string
    {
        $to = [];
        foreach ($recipients as $recipient) {
            $to[] = $recipient->getEmail();
        }
        return implode(', ', $to);
    }

    protected function prepareHeaders(MailInterface $mail): string
    {
        $headers = ['From: ' . $this->getSenderEmail()];

        foreach ($mail->getHeaders() as $name => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }

            if (!strcasecmp($name, 'From')) {
                $headers[0] = "From: $value";
            } else {
                $headers[] = "$name: $value";
            }
        }

        if (!str_contains($headers [0], '@')) {
            unset($headers[0]);
        }

        return implode("\r\n", $headers);
    }
}
