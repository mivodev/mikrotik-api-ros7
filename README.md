# Mikrotik API ROS7 (REST)

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.2-8892BF.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-MIT-green.svg)](LICENSE)

Complete, universal PHP client for **Mikrotik RouterOS v7 REST API**. Framework-agnostic, leveraging native PHP cURL.

## Features

- 🌐 **Modern REST API** — Uses RouterOS v7's native REST interface (HTTP/HTTPS)
- 🔄 **100% Backward Compatible** — The `comm()` method maps legacy ROS6 CLI commands to REST automatically
- 🏗️ **Native HTTP Methods** — Dedicated `get()`, `put()`, `patch()`, `post()`, and `delete()` methods
- 🧩 **Framework-Agnostic** — Works with PHP Native, Laravel, CodeIgniter, Symfony
- 🔐 **SSL Support** — Auto-connects via HTTPS (port 443) with options to verify or bypass self-signed certificates
- 📊 **Auto-Parsing** — JSON responses are automatically parsed and structured

## Requirements

- PHP >= 8.2
- `ext-curl`
- `ext-json`
- Mikrotik RouterOS v7 (with `www-ssl` or `www` service enabled)

## Installation

### Via Composer (Recommended)

```bash
composer require mivodev/mikrotik-api-ros7
```

### For Laravel Projects

Use the Laravel wrapper instead, which provides ServiceProvider, Facade, and `.env` configuration:

```bash
composer require mivodev/laravel-mikrotik-api-ros7
```

## Quick Start

```php
<?php

use Mivo\MikrotikRos7\Client;

$api = new Client();
$api->connect('192.168.1.1', 'admin', 'password');

// Using Native REST Methods
$identity = $api->get('/rest/system/identity');
print_r($identity);

// Using Legacy CLI Mapping
$users = $api->comm('/ip/hotspot/user/print');
print_r($users);
```

## Usage: Native REST

The preferred way to interact with ROS7 is using explicit HTTP methods:

```php
$api = new Client();
$api->connect('192.168.1.1', 'admin', 'password');

// GET (Read)
$users = $api->get('/rest/ip/hotspot/user', ['profile' => 'default']);

// PUT (Create)
$api->put('/rest/ip/hotspot/user', [
    'name' => 'new-user',
    'password' => 'secret123',
    'profile' => 'default'
]);

// PATCH (Update) - Requires the .id
$api->patch('/rest/ip/hotspot/user/*1', [
    'password' => 'new-secret'
]);

// DELETE (Remove)
$api->delete('/rest/ip/hotspot/user/*1');

// POST (Execute Command)
$api->post('/rest/system/reboot');
```

## Usage: Legacy ROS6 CLI Mapping

If you are migrating from ROS6, you don't need to rewrite your code! The `comm()` method automatically translates legacy CLI syntax into the correct REST HTTP requests:

```php
// Translated to: GET /rest/ip/hotspot/user
$users = $api->comm('/ip/hotspot/user/print');

// Translated to: PUT /rest/ip/hotspot/user with JSON payload
$api->comm('/ip/hotspot/user/add', [
    'name' => 'test-user',
    'password' => '123'
]);

// Translated to: PATCH /rest/ip/hotspot/user/*1
$api->comm('/ip/hotspot/user/set', [
    '.id' => '*1',
    'password' => 'new-password'
]);

// Translated to: DELETE /rest/ip/hotspot/user/*1
$api->comm('/ip/hotspot/user/remove', [
    '.id' => '*1'
]);

// Translated to: GET /rest/ip/hotspot/user?name=test-user
$api->comm('/ip/hotspot/user/print', [
    '?name' => 'test-user'
]);
```

## Configuration

```php
$api = new Client();

// Disable SSL certificate verification (useful for router self-signed certs)
$api->verifySsl = false;

// Set connection timeout (seconds)
$api->timeout = 10;

// Enable debug mode (prints cURL commands and JSON payloads)
$api->debug = true;

// Connect via HTTP instead of HTTPS (not recommended for production)
$api->connect('192.168.1.1', 'admin', 'password', 80);
```

## Architecture

```
src/
├── Client.php                   # Main client — uses cURL for REST
├── Contracts/
│   └── ClientInterface.php      # Universal interface (shared with ROS6)
├── Http/
│   ├── RequestBuilder.php       # Maps legacy CLI commands to REST
│   └── ResponseParser.php       # Parses JSON and handles HTTP errors
└── Exceptions/
    └── MikrotikException.php    # Error handling for REST (400, 401, 404, 500)
```

## References

- [Mikrotik REST API Documentation](https://help.mikrotik.com/docs/display/ROS/REST+API)

## License

MIT License. See [LICENSE](LICENSE) for details.