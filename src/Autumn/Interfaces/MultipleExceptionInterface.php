<?php
/**
 * Autumn PHP Framework
 *
 * Date:        26/12/2023
 */

namespace Autumn\Interfaces;

interface MultipleExceptionInterface extends \Throwable
{
    /**
     * @return \Throwable[]
     */
    public function getErrors(): array;
}