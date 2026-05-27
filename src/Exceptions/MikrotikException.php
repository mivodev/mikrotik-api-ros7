<?php

declare(strict_types=1);

namespace Mivo\MikrotikRos7\Exceptions;

use RuntimeException;

/**
 * Exception thrown when a Mikrotik ROS7 REST API operation fails.
 */
class MikrotikException extends RuntimeException
{
    /**
     * Create exception for connection failure (e.g. cURL error).
     */
    public static function connectionFailed(string $host, string $reason = ''): self
    {
        $message = "Failed to connect to {$host} via REST API";
        if ($reason !== '') {
            $message .= " — {$reason}";
        }

        return new self($message);
    }

    /**
     * Create exception for authentication failure (HTTP 401).
     */
    public static function authenticationFailed(string $host): self
    {
        return new self("Authentication failed on {$host}. Check username and password.");
    }

    /**
     * Create exception from a RouterOS REST error response (HTTP 400, 404, 500, etc).
     */
    public static function apiError(string $message, int $statusCode = 400, ?int $errorDetail = null): self
    {
        $prefix = "HTTP {$statusCode}: ";
        $suffix = $errorDetail !== null ? " (Detail: {$errorDetail})" : '';

        return new self("RouterOS REST API Error: {$prefix}{$message}{$suffix}", $statusCode);
    }
}
