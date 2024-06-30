<?php

namespace Autumn\Mailing;

use Autumn\System\Service;

class MailService extends Service implements MailerInterface
{
    private ?MailerInterface $mailer = null;

    /**
     * @return MailerInterface|null
     */
    public function getMailer(): ?MailerInterface
    {
        return $this->mailer ??= make(MailerInterface::class, null, true) ?? new DefaultMailer;
    }

    public function send(MailInterface $mail, array $context = null): bool
    {
        return $this->getMailer()?->send($mail, $context) !== null;
    }
}