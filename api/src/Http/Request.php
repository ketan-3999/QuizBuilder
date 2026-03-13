<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Simple request abstraction (SRP: read input and path only).
 * Depend on this instead of superglobals for testability and clarity.
 */
final class Request
{
    /** @var array */
    private $input;

    /** @var string */
    private $path;


    public function __construct(array $input, string $path = '')
    {
        $this->input = $input;
        $this->path  = $path;
    }

    public function getInput(): array
    {
        return $this->input;
    }

    public function get(string $key, $default = null)
    {
        return array_key_exists($key, $this->input) ? $this->input[$key] : $default;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Build from current PHP request (JSON body or $_POST, path from REQUEST_URI).
     * @return self
     */
    public static function fromGlobals(): self
    {
        $input = self::readInput();
        $path  = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) : '';
        $path  = preg_replace('#^.*/api/#', '/api/', $path);

        if (empty($input['operation_type'])) {
            if ($path === '/api/quiz/generate') {
                $input['operation_type'] = 'generate_quiz';
            } elseif ($path === '/api/quiz/submit') {
                $input['operation_type'] = 'submit_quiz';
            }
        }

        return new self($input, $path);
    }

    /**
     * @return array
     */
    private static function readInput(): array
    {
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';
        if (strpos($contentType, 'application/json') !== false) {
            $body    = file_get_contents('php://input');
            $decoded = json_decode($body ?: '{}', true);
            return is_array($decoded) ? $decoded : array();
        }
        return $_POST;
    }
}
