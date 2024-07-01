<?php

namespace Autumn\System\Requests;

use Autumn\Exceptions\ValidationException;
use Autumn\System\Request;

abstract class BaseRequest implements \ArrayAccess
{
    protected string $fieldPrefix = '';

    /**
     * The validation rules
     *
     * @var array<string, string|array<string, mixed>>
     */
    protected array $rules = [];

    /**
     * The default validation rules
     *
     * @var array
     */
    protected array $defaultRules = [];

    /**
     * The default values
     *
     * @var array<string, mixed>
     */
    protected array $defaults = [];

    /**
     * The values if not found a key
     *
     * @var array<string, mixed>
     */
    protected array $missing = [];

    /**
     * The input data
     *
     * @var array|\ArrayAccess
     */
    private array|\ArrayAccess $request;

    /**
     * The result of validation
     *
     * @var array
     */
    private array $validateResult = [];

    /**
     * Constructor.
     *
     * @param Request|array|\ArrayAccess $request The request containing input data.
     */
    public function __construct(Request|array|\ArrayAccess $request)
    {
        $this->request = $request;
    }

    /**
     * Parse a rule into validator name and arguments.
     *
     * @param string $rule The rule to be parsed
     * @return array<string, array<string>> The result of parsed validator
     */
    public static function parseRule(string $rule): array
    {
        $results = [];

        $length = strlen($rule);

        $escaped = false;

        $temp = '';
        $name = '';
        $args = [];
        for ($i = 0; $i < $length; $i++) {
            $char = $rule[$i];
            if ($escaped) {
                $escaped = false;
                $temp .= $char;
            } elseif ($char === '\\') {
                $escaped = true;
            } elseif ($char === '|') {
                if ($temp !== '') {
                    if ($name === '') {
                        $results[$temp] = $args;
                    } else {
                        $args[] = $temp;
                        $results[$name] = $args;
                    }
                } elseif ($name !== '') {
                    $results[$name] = $args;
                }
                $temp = '';
                $name = '';
                $args = [];
            } elseif ($char === ':') {
                $name = $temp;
                $temp = '';
            } elseif ($char === ',') {
                $args[] = $temp;
                $temp = '';
            } else {
                $temp .= $char;
            }
        }

        if ($temp !== '') {
            if ($name === '') {
                $results[$temp] = $args;
            } else {
                $args[] = $temp;
                $results[$name] = $args;
            }
        } elseif ($name !== '') {
            $results[$name] = $args;
        }

        return $results;
    }

    public function rules(): array
    {
        return array_merge($this->defaultRules, $this->rules);
    }

    public function get(string $name, mixed $default = null): mixed
    {
        return $this->validateResult[$name] ?? $default;
    }

    public function has(string $name): bool
    {
        return isset($this->validateResult[$name]);
    }

    public function set(string $name, mixed $value = null): void
    {
        if ($rule = $this->rules[$name] ?? null) {
            $this->validateWithRule($name, $value, $rule);
        }

        $this->validateResult[$name] = $value;
    }

    public function someOf(array $keys, bool $existingOnly = false): array
    {
        $data = [];
        foreach ($keys as $alias => $key) {
            $value = $this->get($alias) ?? $this->get($key);
            if (!$existingOnly || $value !== null && $value !== '') {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    public function some(string ...$keys): array
    {
        $data = [];
        foreach ($keys as $key) {
            $data[$key] = $this->get($key);
        }
        return $data;
    }

    public function any(string ...$keys): mixed
    {
        foreach ($keys as $key) {
            $value = $this->get($key);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return null;
    }

    public function compact(string ...$keys): array
    {
        $data = [];
        foreach ($keys as $key) {
            $value = $this->get($key);
            if ($value !== null && $value !== '') {
                $data[$key] = $value;
            }
        }
        return $data;
    }

    /**
     * Validates input data against a set of rules and stores the results.
     *
     * This method processes each rule provided in the $rules array, or if no rules
     * are provided, it uses the default set of rules defined in $this->rules.
     *
     * @param array|null $rules Optional. An array of rules to validate against.
     *                          If null, the default $this->rules will be used.
     */
    public function validate(array $rules = null): void
    {
        $this->validateResult = $this->validateRules($rules ?? $this->rules(), $this->fieldPrefix);
    }

    /**
     * Validates an array of rules recursively.
     *
     * This method validates each rule in the provided $rules array recursively,
     * processing nested arrays of rules. It fetches values according to each rule
     * using the fetchWithRule method.
     *
     * @param array $rules An array of rules to validate against.
     * @param string|null $prefix Optional. A prefix to prepend to each rule name.
     * @param array|null $errors Reference variable to collect any validation errors.
     *                            This array will be populated with errors where the keys
     *                            are the field names and the values are the corresponding
     *                            exceptions thrown during validation.
     * @return array An associative array containing validated values according to the rules.
     */
    public function validateRules(array $rules, string $prefix = null, array $errors = null): array
    {
        $prefix = $rules['@prefix'] ?? $prefix;
        unset($rules['@prefix']);

        $result = [];

        foreach ($rules as $name => $rule) {
            if (str_starts_with($name, '@')) {
                continue;
            }

            if (is_array($rule)) {
                $result[$name] = $this->validateRules($rule, $prefix);
            } elseif (isset($errors)) {
                try {
                    $result[$name] = $this->fetchWithRule($prefix . $name, $rule);
                } catch (\Throwable $exception) {
                    $errors[$name] = $exception;
                }
            } else {
                $result[$name] = $this->fetchWithRule($prefix . $name, $rule);
            }
        }

        return $result;
    }

    /**
     * Validates input data against a set of rules and collects errors if any.
     *
     * This method processes each rule provided in the $rules array, or if no rules
     * are provided, it uses the default set of rules defined in $this->rules. It
     * attempts to fetch and validate data according to each rule, storing the results
     * in the $this->validateResult array. If any errors occur during validation,
     * they are collected in the $errors array.
     *
     * @param array|null $rules Optional. An array of rules to validate against.
     *                          If null, the default $this->rules will be used.
     * @return array|null An array of errors if any validation errors occur, or null if validation is successful.
     */
    public function validateAll(array $rules = null): ?array
    {
        // Initialize errors as an empty array
        $errors = [];

        // Reset the validateResult array to store validation results
        $this->validateResult = [];

        // Loop through the provided rules or default to $this->rules
        foreach ($rules ?? $this->rules as $name => $rule) {
            try {
                // Fetch the value using the rule and store it in validateResult
                $value = $this->fetchWithRule($name, $rule);
                $this->validateResult[$name] = $value;
            } catch (\Throwable $error) {
                // Catch any errors and store them in the errors array
                $errors[$name] = $error;
            }
        }

        // If no errors were collected, return null
        return empty($errors) ? null : $errors;
    }

    /**
     * Gets the validation results.
     *
     * @return array The array of validation results.
     */
    public function getValidateResult(): array
    {
        return $this->validateResult;
    }

    /**
     * Authenticate the request.
     *
     * This method is intended to be overridden in subclasses to provide
     * authentication logic.
     */
    public function authenticate(): void
    {
    }

    /**
     * Fetches and validates data according to a given rule.
     *
     * This method retrieves the value associated with the given name, validates it
     * using the specified rule, and returns the value if validation passes.
     *
     * @param string $name The name of the input data.
     * @param string $rule The validation rule.
     * @return mixed The validated value.
     */
    public function fetchWithRule(string $name, string $rule): mixed
    {
        $value = $this->offsetGet($name);
        $this->validateWithRule($name, $value, $rule);
        return $value;
    }

    /**
     * Validates a value according to a given rule.
     *
     * This method applies the specified validation rule to the provided value.
     *
     * @param string $name The name of the input data.
     * @param mixed $value The value to validate.
     * @param string $rule The validation rule.
     */
    public function validateWithRule(string $name, mixed $value, string $rule): void
    {
        foreach (static::parseRule($rule) as $validator => $args) {
            if (method_exists($this, $func = 'ruleOf' . $validator)) {
                $this->$func($name, $value, ...$args);
            }
        }
    }

    // Implementing the ArrayAccess interface methods...

    /**
     * Checks if an offset exists.
     *
     * @param mixed $offset The offset to check for.
     * @return bool True if the offset exists, false otherwise.
     */
    public function offsetExists(mixed $offset): bool
    {
        return isset($this->request[$this->fieldPrefix . $offset]);
    }

    /**
     * Gets the value at the specified offset.
     *
     * @param mixed $offset The offset to retrieve.
     * @return mixed The value at the specified offset.
     */
    public function offsetGet(mixed $offset): mixed
    {
        $value = $this->request[$this->fieldPrefix . $offset] ?? null;

        if ($value === '') {
            $value = $this->defaults[$this->fieldPrefix . $offset] ?? '';
        }

        if ($value === null) {
            $value = $this->missing[$this->fieldPrefix . $offset] ?? '';
        }

        return $value;
    }

    /**
     * Sets the value at the specified offset.
     *
     * Note: Setting the value is not allowed in this implementation. This method
     * is deprecated and does not perform any operation.
     *
     * @param mixed $offset The offset to assign the value to.
     * @param mixed $value The value to set.
     * @deprecated This method is not allowed to set values and is deprecated.
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        // Setting values is not allowed in this implementation.
        // $this->request[$offset] = $value;
    }

    /**
     * Unsets the value at the specified offset.
     *
     * Note: Unsetting the value is not allowed in this implementation. This method
     * is deprecated and does not perform any operation.
     *
     * @param mixed $offset The offset to unset.
     * @deprecated This method is not allowed to unset values and is deprecated.
     */
    public function offsetUnset(mixed $offset): void
    {
        // Unsetting values is not allowed in this implementation.
        // unset($this->request[$offset]);
    }

    /**
     * Validates that a value is not null, performing the same result as of the rule `required`.
     *
     * This rule checks if the provided value is not null, indicating that the field
     * is required.
     *
     *  For example:
     *  $rule = 'require';
     *
     * @param string $name The name of the field being validated.
     * @param mixed $value The value of the field being validated.
     * @throws ValidationException if the value is null.
     */
    protected function ruleOfRequire(string $name, mixed $value): void
    {
        if ($value === null) {
            throw ValidationException::of('`%s` is required.', $name);
        }
    }

    /**
     * Validates that a value is not null, performing the same result as of the rule `require`.
     *
     * This rule checks if the provided value is not null, indicating that the field
     * is required.
     *
     *  For example:
     *  $rule = 'required';
     *
     * @param string $name The name of the field being validated.
     * @param mixed $value The value of the field being validated.
     * @throws ValidationException if the value is null.
     */
    protected function ruleOfRequired(string $name, mixed $value): void
    {
        $this->ruleOfRequire($name, $value);
    }

    /**
     * Validates that a value is a valid email address.
     *
     * This rule checks if the provided value is a valid email address using PHP's
     * FILTER_VALIDATE_EMAIL filter.
     *
     *  For example:
     *  $rule = 'email';
     *
     * @param string $name The name of the field being validated.
     * @param mixed $value The value of the field being validated.
     * @throws ValidationException if the value is not a valid email address.
     */
    protected function ruleOfEmail(string $name, mixed $value): void
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::of('`%s` must be a valid email address.', $name);
        }
    }

    /**
     * Validates that a value is numeric.
     *
     * This rule checks if the provided value is numeric, which includes integers,
     * floats, and numeric strings.
     *
     *  For example:
     *  $rule = 'number';
     *
     * @param string $name The name of the field being validated.
     * @param mixed $value The value of the field being validated.
     * @throws ValidationException if the value is not numeric.
     */
    protected function ruleOfNumber(string $name, mixed $value): void
    {
        $this->ruleOfNumeric($name, $value);
    }


    /**
     * Validates that a value is numeric.
     *
     * This rule checks if the provided value is numeric, which includes integers,
     * floats, and numeric strings.
     *
     *  For example:
     *  $rule = 'numeric';
     *
     * @param string $name The name of the field being validated.
     * @param mixed $value The value of the field being validated.
     * @throws ValidationException if the value is not numeric.
     */
    protected function ruleOfNumeric(string $name, mixed $value): void
    {
        if (!is_numeric($value)) {
            throw ValidationException::of('`%s` must be a numeric value.', $name);
        }
    }

    /**
     * Validates that a value has a minimum length.
     *
     * This rule checks if the provided value has a length greater than or equal to
     * the specified minimum length.
     *
     *  For example:
     *  $rule = 'min:3';
     *
     * @param string $name The name of the field being validated.
     * @param mixed $value The value of the field being validated.
     * @param int $min The minimum length.
     * @throws ValidationException if the value's length is less than the minimum length.
     */
    protected function ruleOfMin(string $name, mixed $value, int $min): void
    {
        if (strlen((string)$value) < $min) {
            throw ValidationException::of('`%s` must be at least %d characters long.', $name, $min);
        }
    }

    /**
     * Validates that a value has a maximum length.
     *
     * This rule checks if the provided value has a length less than or equal to
     * the specified maximum length.
     *
     *  For example:
     *  $rule = 'in:1,2,3';
     *
     * @param string $name The name of the field being validated.
     * @param mixed $value The value of the field being validated.
     * @param int $max The maximum length.
     * @throws ValidationException if the value's length exceeds the maximum length.
     */
    protected function ruleOfMax(string $name, mixed $value, int $max): void
    {
        if (strlen((string)$value) > $max) {
            throw ValidationException::of('`%s` must be no more than %d characters long.', $name, $max);
        }
    }

    /**
     * Validates that a value's length is between a minimum and maximum length, separated by common `,`.
     *
     * This rule checks if the provided value has a length that falls within the
     * specified range, inclusive.
     *
     *  For example:
     *  $rule = 'between:3,5';
     *
     * @param string $name The name of the field being validated.
     * @param mixed $value The value of the field being validated.
     * @param int $min The minimum length.
     * @param int $max The maximum length.
     * @throws ValidationException if the value's length is not within the specified range.
     */
    protected function ruleOfBetween(string $name, mixed $value, int $min, int $max): void
    {
        $length = strlen((string)$value);
        if ($length < $min || $length > $max) {
            throw ValidationException::of('`%s` must be between %d and %d characters long.', $name, $min, $max);
        }
    }

    /**
     * Validates that a value is within an allowed set of values, separated by common `,`.
     *
     * This rule checks if the provided value is one of the specified allowed values.
     *
     *  For example:
     *  $rule = 'in:1,2,3';
     *
     * @param string $name The name of the field being validated.
     * @param mixed $value The value of the field being validated.
     * @param mixed ...$allowed The allowed values.
     * @throws ValidationException if the value is not in the list of allowed values.
     */
    protected function ruleOfIn(string $name, mixed $value, ...$allowed): void
    {
        if (!in_array($value, $allowed, true)) {
            throw ValidationException::of('`%s` must be one of: %s.', $name, implode(', ', $allowed));
        }
    }

    /**
     * Validates that a value is a valid date in a specified format.
     *
     * This rule checks if the provided value is a valid date according to the
     * specified format. The default format is 'Y-m-d'.
     *
     *  For example:
     *  $rule = 'date:d/m/Y';
     *
     * @param string $name The name of the field being validated.
     * @param mixed $value The value of the field being validated.
     * @param string $format The date format to validate against.
     * @throws ValidationException if the value is not a valid date in the specified format.
     */
    protected function ruleOfDate(string $name, mixed $value, string $format = 'Y-m-d'): void
    {
        $format = stripcslashes($format);

        $d = \DateTimeImmutable::createFromFormat($format, $value);
        if (!($d && $d->format($format) === $value)) {
            throw ValidationException::of('`%s` must be a valid date in the format %s.', $name, $format);
        }
    }

    /**
     * Validates that a value is a valid date in a specified format.
     *
     * This rule checks if the provided value is a valid date according to the
     * specified format. The default format is 'H:i:s'.
     *
     *  For example:
     *  $rule = 'time:H\:i A';
     *
     * @param string $name The name of the field being validated.
     * @param mixed $value The value of the field being validated.
     * @param string $format The date format to validate against.
     * @throws ValidationException if the value is not a valid date in the specified format.
     */
    protected function ruleOfTime(string $name, mixed $value, string $format = 'H\:i\:s'): void
    {
        $format = stripcslashes($format);

        $d = \DateTimeImmutable::createFromFormat($format, $value);
        if (!($d && $d->format($format) === $value)) {
            throw ValidationException::of('`%s` must be a valid date in the format %s.', $name, $format);
        }
    }

    /**
     * Validates that a value is a boolean, performing the same result as of the rule `bool`.
     *
     * This rule checks if the provided value is a boolean or can be interpreted
     * as a boolean (0, 1, '0', '1').
     *
     *  For example:
     *  $rule = 'bool';
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    protected function ruleOfBool(string $name, mixed $value): void
    {
        if (!is_bool($value)
            && (filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) === null)) {
            throw ValidationException::of('`%s` must be a boolean value.', $name);
        }
    }

    /**
     * Validates that a value is a boolean, performing the same result as of the rule `bool`.
     *
     * This rule checks if the provided value is a boolean or can be interpreted
     * as a boolean (0, 1, '0', '1').
     *
     *  For example:
     *  $rule = 'boolean';
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    protected function ruleOfBoolean(string $name, mixed $value): void
    {
        $this->ruleOfBool($name, $value);
    }

    /**
     * Validates that a value is the same as the other's.
     *
     * For example:
     * $rule = 'same|password';
     */
    protected function ruleOfSame(string $name, mixed $value, mixed $field): void
    {
        $compare = $this->get($field);
        if ($compare !== $value) {
            throw ValidationException::of(
                'The value of `%s` must be the same as that of `%s`.',
                $field, $name
            );
        }
    }
}