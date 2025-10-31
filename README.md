# Lark OAuth2 Login Bundle

[English](README.md) | [中文](README.zh-CN.md)

[![Latest Version](https://img.shields.io/packagist/v/tourze/lark-oauth2-login-bundle.svg?style=flat-square)](
https://packagist.org/packages/tourze/lark-oauth2-login-bundle)
[![Total Downloads](https://img.shields.io/packagist/dt/tourze/lark-oauth2-login-bundle.svg?style=flat-square)](
https://packagist.org/packages/tourze/lark-oauth2-login-bundle)
[![License: MIT](https://img.shields.io/badge/license-MIT-green.svg?style=flat-square)](LICENSE)
[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-8892BF.svg?style=flat-square)](https://php.net/)
[![Build Status](
https://img.shields.io/github/actions/workflow/status/tourze/php-monorepo/ci.yml?branch=master&style=flat-square)](
https://github.com/tourze/php-monorepo/actions)
[![Code Coverage](https://img.shields.io/codecov/c/github/tourze/php-monorepo?style=flat-square)](
https://codecov.io/gh/tourze/php-monorepo)

## Introduction

This Symfony bundle provides OAuth2 authentication integration with Lark (Feishu) for web
applications, allowing users to log in using their Lark accounts.

## Features

- Complete OAuth2 authentication flow implementation
- Support for PKCE (Proof Key for Code Exchange) flow
- Automatic token refresh mechanism
- User information synchronization
- Multiple Lark app configuration support
- Built-in CSRF protection with state parameter
- Comprehensive error handling and logging

## Installation

### Requirements

- PHP >= 8.1
- Symfony >= 6.4
- Doctrine ORM >= 3.0

### Install

```bash
composer require tourze/lark-oauth2-login-bundle
```

### Configuration

1. Register the bundle in `config/bundles.php`:

```php
Tourze\LarkOAuth2LoginBundle\LarkOAuth2LoginBundle::class => ['all' => true],
```

2. Run database migrations to create required tables.

## Quick Start

### 1. Create Lark OAuth2 Configuration

```php
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;

$config = new LarkOAuth2Config();
$config->setAppId('your-lark-app-id');
$config->setAppSecret('your-lark-app-secret');
$config->setName('Main Lark App');
$config->setScope('contact:user.base:readonly offline_access');
$config->setValid(true);

$entityManager->persist($config);
$entityManager->flush();
```

### 2. Configure Redirect URL in Lark Console

Add the following redirect URL to your Lark app configuration:
```text
https://your-domain.com/lark-oauth2/callback
```

### 3. Initiate Login

Direct users to the login endpoint:
```text
/lark-oauth2/login
```

### 4. Handle Login Success

After successful authentication, the user information will be stored in `LarkOAuth2User` entity.

```php
use Tourze\LarkOAuth2LoginBundle\Repository\LarkOAuth2UserRepository;

// Get user by OpenID
$user = $userRepository->findByOpenId($openId);

// Access user information
$name = $user->getName();
$email = $user->getEmail();
$avatarUrl = $user->getAvatarUrl();
```

## API Endpoints

- **Login**: `GET /lark-oauth2/login`
  - Optional query parameters:
    - `code_challenge`: For PKCE flow
    - `code_challenge_method`: Either 'S256' or 'plain'

- **Callback**: `GET /lark-oauth2/callback`
  - Handled automatically by the bundle

## Advanced Usage

### Token Refresh

```php
use Tourze\LarkOAuth2LoginBundle\Service\LarkOAuth2Service;

// Refresh token for a specific user
$success = $oauth2Service->refreshToken($openId);

// Refresh all expired tokens
$refreshedCount = $oauth2Service->refreshExpiredTokens();
```

### Fetch Latest User Info

```php
// Force refresh user information from Lark
$userInfo = $oauth2Service->fetchUserInfo($openId, true);
```

### Clean Up Expired States

```php
// Remove expired authorization states
$deletedCount = $oauth2Service->cleanupExpiredStates();
```

## Error Handling

The bundle provides three exception types:

- `LarkOAuth2Exception`: Base exception for all OAuth2 errors
- `LarkOAuth2ConfigurationException`: Configuration-related errors
- `LarkOAuth2ApiException`: API call errors

## Security

- State parameter validation to prevent CSRF attacks
- Automatic cleanup of expired states
- Secure token storage with expiration tracking
- Support for offline access with refresh tokens

## Contributing

We welcome contributions to improve this bundle. To contribute:

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Commit your changes: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin feature/your-feature`
5. Submit a pull request

### Code Style

- Follow PSR-12 coding standards
- Add tests for new functionality
- Update documentation as needed

### Testing

Run the test suite:
```bash
./vendor/bin/phpunit packages/lark-oauth2-login-bundle/tests
```

## Changelog

### v0.1.0 (Current)

- Initial release
- OAuth2 authentication flow implementation
- PKCE support
- Token refresh mechanism
- User information synchronization
- Multiple app configuration support
- CSRF protection with state parameter
- Comprehensive error handling

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
