<?php

declare(strict_types=1);

namespace Backend\Support;

final class Response
{
    /**
     * @param mixed $data
     */
    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    public static function error(string $message, int $status = 400, array $errors = []): never
    {
        $payload = ['error' => $message];
        if ($errors !== []) {
            $payload['details'] = $errors;
        }
        self::json($payload, $status);
    }
}
