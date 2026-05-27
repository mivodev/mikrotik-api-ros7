<?php

declare(strict_types=1);

namespace Mivo\MikrotikRos7\Http;

use InvalidArgumentException;

/**
 * Helper to build REST HTTP requests and translate legacy CLI commands.
 */
class RequestBuilder
{
    /**
     * Translate a ROS6 CLI command into a REST HTTP request.
     *
     * @param  string  $command  The CLI command (e.g. /ip/hotspot/user/print)
     * @param  array   $params   The CLI parameters
     *
     * @return array{method: string, endpoint: string, payload: array}
     */
    public static function translateCommand(string $command, array $params = []): array
    {
        $command = trim($command, '/');
        $parts = explode('/', $command);
        $action = array_pop($parts);
        $baseEndpoint = '/rest/' . implode('/', $parts);

        $payload = [];
        $queryParams = [];
        $id = null;

        // Separate query params (?), IDs (.id), and standard attributes
        foreach ($params as $key => $value) {
            if ($key === '.id') {
                $id = $value;
                continue;
            }

            if (str_starts_with($key, '?')) {
                $queryParams[substr($key, 1)] = $value;
                continue;
            }

            // Remove regex modifier (~) for REST (REST uses direct exact matches or its own syntax)
            if (str_starts_with($key, '~')) {
                $queryParams[substr($key, 1)] = $value;
                continue;
            }

            $payload[$key] = $value;
        }

        $endpoint = $baseEndpoint;
        if ($id !== null && in_array($action, ['set', 'remove', 'print'], true)) {
            $endpoint .= '/' . $id;
        }

        if (!empty($queryParams) && $action === 'print') {
            $endpoint .= '?' . http_build_query($queryParams);
        }

        $method = match ($action) {
            'print'  => 'GET',
            'add'    => 'PUT',
            'set'    => 'PATCH',
            'remove' => 'DELETE',
            default  => 'POST',
        };

        // If the action wasn't a standard CRUD action (like reboot, enable, disable),
        // we send the payload via POST to /rest/path/action
        if ($method === 'POST') {
            $endpoint = '/rest/' . $command;
        }

        return [
            'method'   => $method,
            'endpoint' => $endpoint,
            'payload'  => $payload,
        ];
    }
}
