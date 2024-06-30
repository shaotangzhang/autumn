<?php

namespace Autumn\Mailing;
interface RecipientInterface
{
    public function getName(): string;

    public function getEmail(): string;

    public function getType(): RecipientTypeEnum;
}