<?php

namespace Autumn\Database\Interfaces;

interface Extendable
{
    public static function relation_primary_class(): string;

    public static function relation_primary_column(): string;

    public static function relation_secondary_column(): ?string;

}