<?php

namespace Autumn\Security;

interface CsrfTokenInterface
{
    public function verify(string $token): bool;

    public function getInputName(): string;

    public function getInputValue(): string;

    public function getHeaderName(): string;

    public function getHeaderValue(): string;
}