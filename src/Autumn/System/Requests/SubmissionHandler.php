<?php
/**
 * Autumn PHP Framework
 *
 * Date:        18/06/2024
 */

namespace Autumn\System\Requests;

interface SubmissionHandler
{
    public function submit(FormRequest $form, array $context = null): mixed;

    public function toSubmit(FormRequest $form, array $context = null): callable;
}