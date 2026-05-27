<?php

declare(strict_types=1);

namespace Mivo\MikrotikRos7\Contracts;

/**
 * Contract for Mikrotik RouterOS v7 REST API clients.
 *
 * This interface provides explicit HTTP methods (get, put, patch, post, delete)
 * but also implements the universal comm() method for full backward
 * compatibility with the ROS6 Socket CLI syntax.
 *
 * @see https://help.mikrotik.com/docs/display/ROS/REST+API
 */
interface ClientInterface
{
    /**
     * Set connection credentials and host.
     * Unlike ROS6 sockets, REST is stateless. This just configures the client.
     *
     * @param  string  $host  IP address or hostname of the router.
     * @param  string  $username  RouterOS username.
     * @param  string  $password  RouterOS password.
     * @param  int  $port  API port (default: 443 for HTTPS, 80 for HTTP).
     */
    public function connect(string $host, string $username = 'admin', string $password = '', int $port = 443): bool;

    /**
     * Clear credentials.
     */
    public function disconnect(): void;

    /**
     * Whether client is configured.
     */
    public function isConnected(): bool;

    /**
     * Make a GET request (equivalent to print).
     */
    public function get(string $endpoint, array $params = []): array;

    /**
     * Make a PUT request (equivalent to add).
     */
    public function put(string $endpoint, array $payload = []): array;

    /**
     * Make a PATCH request (equivalent to set).
     */
    public function patch(string $endpoint, array $payload = []): array;

    /**
     * Make a POST request (equivalent to executing commands without return data).
     */
    public function post(string $endpoint, array $payload = []): array;

    /**
     * Make a DELETE request (equivalent to remove).
     */
    public function delete(string $endpoint, array $payload = []): array;

    /**
     * Execute a RouterOS CLI command using the REST API.
     *
     * This method automatically translates ROS6-style CLI commands
     * into the appropriate REST API HTTP methods and endpoints.
     *
     * Examples:
     *   comm('/ip/hotspot/user/print') -> GET /rest/ip/hotspot/user
     *   comm('/ip/hotspot/user/add', ['name' => 'test']) -> PUT /rest/ip/hotspot/user
     *   comm('/ip/hotspot/user/set', ['.id' => '*1', 'comment' => 'x']) -> PATCH /rest/ip/hotspot/user/*1
     *   comm('/ip/hotspot/user/remove', ['.id' => '*1']) -> DELETE /rest/ip/hotspot/user/*1
     *   comm('/system/reboot') -> POST /rest/system/reboot
     *
     * @param  string  $command  RouterOS CLI command path.
     * @param  array  $params  Parameters.
     */
    public function comm(string $command, array $params = []): array;
}
