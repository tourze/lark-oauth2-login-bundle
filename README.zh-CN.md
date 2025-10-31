# 飞书 OAuth2 登录 Bundle

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

## 简介

这个 Symfony Bundle 为 Web 应用提供了飞书 OAuth2 认证集成，允许用户使用飞书账号登录。

## 功能特性

- 完整的 OAuth2 认证流程实现
- 支持 PKCE（Proof Key for Code Exchange）流程
- 自动令牌刷新机制
- 用户信息同步
- 支持多个飞书应用配置
- 内置 CSRF 保护（使用 state 参数）
- 全面的错误处理和日志记录

## 安装

### 要求

- PHP >= 8.1
- Symfony >= 6.4
- Doctrine ORM >= 3.0

### 安装

```bash
composer require tourze/lark-oauth2-login-bundle
```

### 配置

1. 在 `config/bundles.php` 中注册 Bundle：

```php
Tourze\LarkOAuth2LoginBundle\LarkOAuth2LoginBundle::class => ['all' => true],
```

2. 运行数据库迁移以创建所需的表。

## 快速开始

### 1. 创建飞书 OAuth2 配置

```php
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;

$config = new LarkOAuth2Config();
$config->setAppId('your-lark-app-id');
$config->setAppSecret('your-lark-app-secret');
$config->setName('主飞书应用');
$config->setScope('contact:user.base:readonly offline_access');
$config->setValid(true);

$entityManager->persist($config);
$entityManager->flush();
```

### 2. 在飞书控制台配置重定向 URL

在飞书应用配置中添加以下重定向 URL：
```text
https://your-domain.com/lark-oauth2/callback
```

### 3. 发起登录

将用户引导到登录端点：
```text
/lark-oauth2/login
```

### 4. 处理登录成功

认证成功后，用户信息将存储在 `LarkOAuth2User` 实体中。

```php
use Tourze\LarkOAuth2LoginBundle\Repository\LarkOAuth2UserRepository;

// 通过 OpenID 获取用户
$user = $userRepository->findByOpenId($openId);

// 访问用户信息
$name = $user->getName();
$email = $user->getEmail();
$avatarUrl = $user->getAvatarUrl();
```

## API 端点

- **登录**: `GET /lark-oauth2/login`
  - 可选查询参数：
    - `code_challenge`: 用于 PKCE 流程
    - `code_challenge_method`: 'S256' 或 'plain'

- **回调**: `GET /lark-oauth2/callback`
  - 由 Bundle 自动处理

## 高级用法

### 令牌刷新

```php
use Tourze\LarkOAuth2LoginBundle\Service\LarkOAuth2Service;

// 刷新特定用户的令牌
$success = $oauth2Service->refreshToken($openId);

// 刷新所有过期的令牌
$refreshedCount = $oauth2Service->refreshExpiredTokens();
```

### 获取最新用户信息

```php
// 强制从飞书刷新用户信息
$userInfo = $oauth2Service->fetchUserInfo($openId, true);
```

### 清理过期状态

```php
// 删除过期的授权状态
$deletedCount = $oauth2Service->cleanupExpiredStates();
```

## 错误处理

Bundle 提供三种异常类型：

- `LarkOAuth2Exception`: 所有 OAuth2 错误的基础异常
- `LarkOAuth2ConfigurationException`: 配置相关错误
- `LarkOAuth2ApiException`: API 调用错误

## 安全性

- 使用 state 参数验证防止 CSRF 攻击
- 自动清理过期状态
- 安全的令牌存储与过期跟踪
- 支持离线访问与刷新令牌

## 贡献

我们欢迎您为改进这个 Bundle 做出贡献：

1. Fork 代码库
2. 创建功能分支：`git checkout -b feature/your-feature`
3. 提交更改：`git commit -am 'Add some feature'`
4. 推送到分支：`git push origin feature/your-feature`
5. 提交拉取请求

### 代码风格

- 遵循 PSR-12 编码标准
- 为新功能添加测试
- 根据需要更新文档

### 测试

运行测试套件：
```bash
./vendor/bin/phpunit packages/lark-oauth2-login-bundle/tests
```

## 更新日志

### v0.1.0 (当前版本)

- 首次发布
- OAuth2 认证流程实现
- PKCE 支持
- 令牌刷新机制
- 用户信息同步
- 多应用配置支持
- 使用 state 参数的 CSRF 保护
- 全面的错误处理

## 许可证

MIT License (MIT)。更多信息请参阅 [许可证文件](LICENSE)。
