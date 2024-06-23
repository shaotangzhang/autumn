<?php

namespace Autumn\Attributes;

use Attribute;

#[Attribute(Attribute::TARGET_METHOD)]
class Loggable
{
    public function __construct(public string $level = 'info')
    {
    }
}
