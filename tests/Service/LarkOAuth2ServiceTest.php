<?php

namespace Tourze\LarkOAuth2LoginBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\LarkOAuth2LoginBundle\Service\LarkOAuth2Service;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(LarkOAuth2Service::class)]
#[RunTestsInSeparateProcesses]
final class LarkOAuth2ServiceTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 这个测试类不需要额外的设置
    }

    public function testServiceCanBeCreated(): void
    {
        $service = self::getService(LarkOAuth2Service::class);
        $this->assertInstanceOf(LarkOAuth2Service::class, $service);
    }

    public function testGetBaseUrl(): void
    {
        $service = self::getService(LarkOAuth2Service::class);
        $this->assertEquals('https://open.feishu.cn', $service->getBaseUrl());
    }

    public function testCleanupExpiredStates(): void
    {
        $service = self::getService(LarkOAuth2Service::class);
        $count = $service->cleanupExpiredStates();
        $this->assertIsInt($count);
    }

    public function testRefreshExpiredTokens(): void
    {
        $service = self::getService(LarkOAuth2Service::class);
        $count = $service->refreshExpiredTokens();
        $this->assertIsInt($count);
    }

    public function testFetchUserInfo(): void
    {
        $this->expectException(\Exception::class);
        $service = self::getService(LarkOAuth2Service::class);
        $service->fetchUserInfo('invalid-open-id');
    }

    public function testGenerateAuthorizationUrl(): void
    {
        $service = self::getService(LarkOAuth2Service::class);
        $url = $service->generateAuthorizationUrl();

        $this->assertStringContainsString('https://accounts.feishu.cn/open-apis/authen/v1/authorize', $url);
        $this->assertStringContainsString('client_id=cli_a1b2c3d4e5f6g7h8', $url);
        $this->assertStringContainsString('state=', $url);
    }

    public function testHandleCallback(): void
    {
        $this->expectException(\Exception::class);
        $service = self::getService(LarkOAuth2Service::class);
        $service->handleCallback('invalid-code', 'invalid-state');
    }

    public function testRefreshToken(): void
    {
        $service = self::getService(LarkOAuth2Service::class);
        $result = $service->refreshToken('invalid-open-id');
        $this->assertFalse($result);
    }
}
