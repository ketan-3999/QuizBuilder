<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Single place for API response shape (QueryCode, QueryMsg, data).
 * Ensures consistent structure and SRP for response formatting.
 */
final class ApiResponse
{
    /**
     * Success response with payload.
     * @param int $code
     * @param string $message
     * @param array|null $data
     * @return array
     */
    public static function success(int $code, string $message, $data): array
    {
        return array(
            'QueryCode' => $code,
            'QueryMsg'  => $message,
            'data'      => $data,
        );
    }

    /**
     * Error response (validation or business logic).
     * @param int $code
     * @param string $message
     * @return array
     */
    public static function error(int $code, string $message): array
    {
        return array(
            'QueryCode' => $code,
            'QueryMsg'  => $message,
            'data'      => null,
        );
    }

    /**
     * Exception / server error response.
     * @param int $code
     * @param string $message
     * @return array
     */
    public static function exception(int $code, string $message): array
    {
        return array(
            'QueryCode' => $code,
            'QueryMsg'  => $message,
            'data'      => null,
        );
    }
}
