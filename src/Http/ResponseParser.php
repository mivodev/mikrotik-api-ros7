<?php

declare(strict_types=1);

namespace Mivo\MikrotikRos7\Http;

use Mivo\MikrotikRos7\Exceptions\MikrotikException;

/**
 * Parses JSON responses from the Mikrotik REST API.
 */
class ResponseParser
{
    /**
     * Parse the HTTP response.
     *
     * @param  string  $body        The raw JSON response body.
     * @param  int     $statusCode  The HTTP status code.
     *
     * @return array  The parsed data as an array.
     *
     * @throws MikrotikException  If the response indicates an error.
     */
    public static function parse(string $body, int $statusCode): array
    {
        $data = [];

        if ($body !== '') {
            $data = json_decode($body, true) ?? [];
        }

        // Handle HTTP errors
        if ($statusCode >= 400) {
            if ($statusCode === 401) {
                throw MikrotikException::authenticationFailed('router');
            }

            $message = $data['error'] ?? 'Unknown API Error';
            $detail = $data['detail'] ?? null;

            // Sometimes the API returns a string message, sometimes an array
            if (is_array($message)) {
                $message = json_encode($message);
            }

            throw MikrotikException::apiError((string) $message, $statusCode, is_numeric($detail) ? (int) $detail : null);
        }

        // The REST API usually wraps lists in arrays.
        // If it returns a single object or empty, we wrap it to maintain
        // compatibility with the ROS6 Socket structure (which always returns arrays of rows).
        if (!is_array($data) || (empty($data) && $body !== '[]')) {
            return $data !== null && $data !== '' ? [$data] : [];
        }

        // If it's an associative array, it's a single row response, wrap it in an array
        // (unless we are just returning standard status which we can leave as is)
        if (!empty($data) && self::isAssoc($data)) {
            return [$data];
        }

        return $data;
    }

    /**
     * Check if an array is associative.
     */
    private static function isAssoc(array $arr): bool
    {
        if (empty($arr)) {
            return false;
        }

        return array_keys($arr) !== range(0, count($arr) - 1);
    }
}
