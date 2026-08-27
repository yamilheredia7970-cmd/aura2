<?php

declare(strict_types=1);

namespace Backend\Support;

final class Validator
{
    /**
     * @param array<string, string> $rules e.g. ['email' => 'required|email', 'password' => 'required|min:8']
     * @return array<string, mixed> sanitized values, keyed like $rules
     * @throws ValidationException
     */
    public static function validate(array $data, array $rules): array
    {
        $errors = [];
        $clean = [];

        foreach ($rules as $field => $ruleString) {
            $value = $data[$field] ?? null;
            if (is_string($value)) {
                $value = trim($value);
            }

            foreach (explode('|', $ruleString) as $rule) {
                [$name, $param] = array_pad(explode(':', $rule, 2), 2, null);

                if ($name === 'required' && ($value === null || $value === '')) {
                    $errors[$field][] = 'This field is required.';
                    continue 2;
                }

                if ($value === null || $value === '') {
                    continue;
                }

                match ($name) {
                    'email' => filter_var($value, FILTER_VALIDATE_EMAIL) === false
                        && $errors[$field][] = 'Invalid email.',
                    'min' => mb_strlen((string) $value) < (int) $param
                        && $errors[$field][] = "Must be at least {$param} characters.",
                    'max' => mb_strlen((string) $value) > (int) $param
                        && $errors[$field][] = "Must be at most {$param} characters.",
                    'numeric' => !is_numeric($value)
                        && $errors[$field][] = 'Must be numeric.',
                    'int' => filter_var($value, FILTER_VALIDATE_INT) === false
                        && $errors[$field][] = 'Must be an integer.',
                    'in' => !in_array($value, explode(',', (string) $param), true)
                        && $errors[$field][] = 'Value not allowed.',
                    default => null,
                };
            }

            $clean[$field] = $value;
        }

        if ($errors !== []) {
            throw new ValidationException($errors);
        }

        return $clean;
    }
}
