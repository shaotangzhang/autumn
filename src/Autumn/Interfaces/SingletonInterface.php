<?php

namespace Autumn\Interfaces;

interface SingletonInterface
{
    public static function getInstance(): static;
}