# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- `Client` class utilizing native PHP cURL for interacting with Mikrotik ROS7 REST API
- Explicit HTTP methods: `get()`, `put()`, `patch()`, `post()`, `delete()`
- `comm()` method backward compatibility layer (maps ROS6 legacy CLI syntax to modern REST endpoints/methods)
- `ClientInterface` contract (framework-agnostic)
- `RequestBuilder` for intelligent CLI to REST translation (including queries, `.id` parsing, and payload stripping)
- `ResponseParser` for JSON parsing and robust HTTP error handling (400, 401, 404, 500)
- `MikrotikException` with detailed static factory methods for API errors and connection failures
- Configurable SSL verification (`verifySsl = false`) for self-signed router certificates
- Debug mode for printing cURL commands and JSON payloads
