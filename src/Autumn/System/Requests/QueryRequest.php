<?php
/**
 * Autumn PHP Framework
 *
 * Date:        18/06/2024
 */

namespace Autumn\System\Requests;

class QueryRequest extends BaseRequest
{
    protected array $defaults = [
        'limit' => 0
    ];

    protected array $rules = [
        'limit' => 'min:0'
    ];

    /**
     * Filters and validates input data against a set of rules.
     *
     * This method processes each rule provided in the $rules array, or if no rules
     * are provided, it uses the default set of rules defined in $this->rules. It
     * attempts to fetch and validate data according to each rule, storing the results
     * in the $result array. If any errors occur during validation, they are collected
     * in the $errors array.
     *
     * @param array|null $rules Optional. An array of rules to validate against.
     *                          If null, the default $this->rules will be used.
     * @param array|null $errors Reference variable to collect any validation errors.
     *                           This array will be populated with errors where the keys
     *                           are the field names and the values are the corresponding
     *                           exceptions thrown during validation.
     * @return array An array of filtered and validated data where the keys are the
     *               field names and the values are the validated values.
     */
    public function filter(array $rules = null, array &$errors = null): array
    {
        $result = [];

        foreach ($rules ?? $this->rules as $name => $rule) {
            try {
                $value = $this->fetchWithRule($name, $rule);
                $result[$name] = $value;
            } catch (\Throwable $error) {
                $errors[$name] = $error;
            }
        }

        return $result;
    }
}