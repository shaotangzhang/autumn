<?php

namespace Autumn\System;

use Autumn\Interfaces\ContextInterface;
use Autumn\System\Requests\FormRequest;
use Autumn\System\Requests\SubmissionHandler;
use Autumn\Traits\ContextInterfaceTrait;

/**
 * Class Service
 *
 * This class serves as a service layer that implements the ContextInterface and SubmissionHandler
 * for handling form submissions within the Autumn system.
 */
class Service implements ContextInterface, SubmissionHandler
{
    use ContextInterfaceTrait;

    /**
     * @var array SUBMISSION_FORMS
     *
     * A constant array that maps form request classes to their respective submission handler methods.
     */
    public const SUBMISSION_FORMS = [];

    /**
     * Prepare a callable for form submission.
     *
     * This method returns a closure that can be used to submit a form request.
     *
     * @param FormRequest $form The form request to be submitted.
     * @param array|null $context Optional. Additional context information for the submission.
     * @return callable A callable that executes the submit method.
     */
    public function toSubmit(FormRequest $form, array $context = null): callable
    {
        return fn() => $this->submit($form, $context);
    }

    /**
     * Submit the form request.
     *
     * This method handles the submission of a form request by delegating it to the appropriate
     * submission handler method based on the form request class type. The submission handlers are
     * defined in the SUBMISSION_FORMS constant.
     *
     * @param FormRequest $form The form request to be submitted.
     * @param array|null $context Optional. Additional context information for the submission.
     * @return mixed The result of the submission handler method.
     * @throws \InvalidArgumentException If the form type is not valid for submission.
     */
    public function submit(FormRequest $form, array $context = null): mixed
    {
        foreach (static::SUBMISSION_FORMS as $class => $action) {
            if (method_exists($this, $action)) {
                if ($form instanceof $class) {
                    $form->authenticate();
                    $form->validate();
                    return call_user_func([$this, $action], $form, $context);
                }
            }
        }

        throw new \InvalidArgumentException('Invalid form type for submission.');
    }
}
