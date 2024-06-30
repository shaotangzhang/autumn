<?php

namespace Autumn\Mailing;

interface MailerInterface
{
    public function send(MailInterface $mail, array $context = null): bool;
}