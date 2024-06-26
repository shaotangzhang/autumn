<?php
/**
 * Autumn PHP Framework
 *
 * Date:        24/06/2024
 */

namespace Autumn\System\Templates;

use Autumn\System\View;

interface TemplateEngineInterface
{
    public function outputView(View $view, array $context=null): void;
}