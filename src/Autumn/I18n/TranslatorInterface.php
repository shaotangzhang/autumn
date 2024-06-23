<?php
/**
 * Autumn PHP Framework
 *
 * Date:        14/06/2024
 */

namespace Autumn\I18n;

interface TranslatorInterface extends Translatable
{
    public function getLanguage(): string;
}