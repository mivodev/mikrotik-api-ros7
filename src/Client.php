<?php

declare(strict_types=1);

namespace Mivo\MikrotikRos7;

use Mivo\MikrotikRos7\Contracts\ClientInterface;
use Mivo\MikrotikRos7\Exceptions\MikrotikException;
use Mivo\MikrotikRos7\Http\RequestBuilder;
use Mivo\MikrotikRos7\Http\ResponseParser;

/**
 * Complete, universal client for Mikrotik RouterOS v7 REST API.
 *
 * FRAMEWORK-AGNOSTIC: Works in PHP Native, Laravel, CodeIgniter, Symfony, etc.
 *
 * This client provides native REST methods (get, put, patch, post, delete)
 * and a `comm()` method for full backward compatibility with ROS6 Socket scripts.
 *
 * Usage:
 *   $api = new Client();
 *   $api->connect('192.168.1.1', 'admin', 'password');
 *
 *   // Native REST
 *   $users = $api->get('/rest/ip/hotspot/user');
 *   $api->put('/rest/ip/hotspot/user', ['name' => 'test', 'password' => '123']);
 *
 *   // Legacy ROS6 Socket Compatibility
 *   $queues = $api->comm('/queue/simple/print');
 *   $api->comm('/ip/hotspot/user/add', ['name' => 'test2']);
 *
 * @see https://help.mikrotik.com/docs/display/ROS/REST+API
 */
class Client implements ClientInterface
{
    protected string $host = '';

    protected string $username = '';

    protected string $password = '';

    protected int $port = 443;

    protected string $scheme = 'https';

    /**
     * Enable/disable debug output.
     */
    public bool $debug = false;

    /**
     * Connection timeout in seconds.
     */
    public int $timeout = 10;

    /**
     * Verify SSL certificates.
     * Set to false if using self-signed router certificates.
     */
    public bool $verifySsl = false;

    /**
     * {@inheritdoc}
     */
    public function connect(string $host, string $username = 'admin', string $password = '', int $port = 443): bool
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        $this->port = $port;
        $this->scheme = ($port === 443 || $port === 8729) ? 'https' : 'http';

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function disconnect(): void
    {
        $this->host = '';
        $this->username = '';
        $this->password = '';
    }

    /**
     * {@inheritdoc}
     */
    public function isConnected(): bool
    {
        return $this->host !== '' && $this->username !== '';
    }

    /**
     * {@inheritdoc}
     */
    public function get(string $endpoint, array $params = []): array
    {
        if (! empty($params)) {
            $endpoint .= (str_contains($endpoint, '?') ? '&' : '?').http_build_query($params);
        }

        return $this->request('GET', $endpoint);
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $endpoint, array $payload = []): array
    {
        return $this->request('PUT', $endpoint, $payload);
    }

    /**
     * {@inheritdoc}
     */
    public function patch(string $endpoint, array $payload = []): array
    {
        return $this->request('PATCH', $endpoint, $payload);
    }

    /**
     * {@inheritdoc}
     */
    public function post(string $endpoint, array $payload = []): array
    {
        return $this->request('POST', $endpoint, $payload);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $endpoint, array $payload = []): array
    {
        return $this->request('DELETE', $endpoint, $payload);
    }

    /**
     * {@inheritdoc}
     */
    public function comm(string $command, array $params = []): array
    {
        $request = RequestBuilder::translateCommand($command, $params);

        return $this->request($request['method'], $request['endpoint'], $request['payload']);
    }

    /**
     * Execute the HTTP request using cURL.
     *
     * @param  string  $method  HTTP Method (GET, POST, PUT, PATCH, DELETE)
     * @param  string  $endpoint  Endpoint (e.g., /rest/ip/address)
     * @param  array  $payload  Data payload
     * @return array Parsed JSON response
     */
    protected function request(string $method, string $endpoint, array $payload = []): array
    {
        if (! $this->isConnected()) {
            throw MikrotikException::connectionFailed('unknown', 'Client is not connected. Call connect() first.');
        }

        $url = "{$this->scheme}://{$this->host}:{$this->port}/".ltrim($endpoint, '/');

        $this->debug(">>> [REST] {$method} {$url}");

        $ch = curl_init();

        $headers = [
            'Accept: application/json',
        ];

        if (! empty($payload)) {
            $jsonPayload = json_encode($payload);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonPayload);
            $headers[] = 'Content-Type: application/json';
            $this->debug(">>> [PAYLOAD] {$jsonPayload}");
        }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");

        if (! $this->verifySsl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        }

        $response = curl_exec($ch);
        $statusCode = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($response === false) {
            throw MikrotikException::connectionFailed($this->host, $error);
        }

        $this->debug("<<< [REST RESPONSE {$statusCode}] {$response}");

        return ResponseParser::parse($response, $statusCode);
    }

    /**
     * Print debug message if debugging is enabled.
     */
    protected function debug(string $message): void
    {
        if ($this->debug) {
            echo $message."\n";
        }
    }
}
