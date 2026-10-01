<?php

class JsonResponse
{
    /**
     * Pure function: returns the JSON string without side effects.
     * Used by tests and by send().
     */
    public static function render(array $payload): string
    {
        return json_encode($payload, JSON_NUMERIC_CHECK);
    }

    /**
     * Sends a JSON response and terminates the script.
     */
    public static function send(array $payload, int $status = 200): void
    {
        http_response_code($status);
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo self::render($payload);
        exit;
    }

    /**
     * Sends an error response and terminates the script.
     */
    public static function error(string $message, int $status = 500, array $extra = []): void
    {
        self::send(array_merge(['error' => $message], $extra), $status);
    }
}