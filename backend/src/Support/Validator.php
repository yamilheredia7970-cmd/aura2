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
                    $errors[$field][] = 'Este campo es obligatorio.';
                    continue 2;
                }

                if ($value === null || $value === '') {
                    continue;
                }

                match ($name) {
                    'email' => filter_var($value, FILTER_VALIDATE_EMAIL) === false
                        && $errors[$field][] = 'Email inválido.',
                    'min' => mb_strlen((string) $value) < (int) $param
                        && $errors[$field][] = "Debe tener al menos {$param} caracteres.",
                    'max' => mb_strlen((string) $value) > (int) $param
                        && $errors[$field][] = "Debe tener como máximo {$param} caracteres.",
                    'numeric' => !is_numeric($value)
                        && $errors[$field][] = 'Debe ser numérico.',
                    'int' => filter_var($value, FILTER_VALIDATE_INT) === false
                        && $errors[$field][] = 'Debe ser un número entero.',
                    'in' => !in_array($value, explode(',', (string) $param), true)
                        && $errors[$field][] = 'Valor no permitido.',
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
