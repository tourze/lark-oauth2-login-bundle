<?php

namespace Tourze\LarkOAuth2LoginBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2User;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(LarkOAuth2User::class)]
final class LarkOAuth2UserTest extends AbstractEntityTestCase
{
    private LarkOAuth2Config $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = new LarkOAuth2Config();
        $this->config->setAppId('test_app_id');
        $this->config->setAppSecret('test_app_secret');
    }

    protected function createEntity(): LarkOAuth2User
    {
        $user = new LarkOAuth2User();
        $user->setConfig($this->config);
        $user->setOpenId('test_open_id');
        $user->setAccessToken('test_access_token');
        $user->setExpiresIn(3600);

        return $user;
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        $config = new LarkOAuth2Config();
        $config->setAppId('test_app_id');
        $config->setAppSecret('test_app_secret');

        yield 'openId' => ['openId', 'test_open_id'];
        yield 'unionId' => ['unionId', 'test_union_id'];
        yield 'userId' => ['userId', 'test_user_id'];
        yield 'name' => ['name', 'Test User'];
        yield 'enName' => ['enName', 'Test User En'];
        yield 'avatarUrl' => ['avatarUrl', 'https://example.com/avatar.jpg'];
        yield 'avatarThumb' => ['avatarThumb', 'https://example.com/thumb.jpg'];
        yield 'avatarMiddle' => ['avatarMiddle', 'https://example.com/middle.jpg'];
        yield 'avatarBig' => ['avatarBig', 'https://example.com/big.jpg'];
        yield 'email' => ['email', 'test@example.com'];
        yield 'mobile' => ['mobile', '+1234567890'];
        yield 'tenantKey' => ['tenantKey', 'test_tenant_key'];
        yield 'employeeNo' => ['employeeNo', 'EMP001'];
        yield 'accessToken' => ['accessToken', 'test_access_token'];
        yield 'refreshToken' => ['refreshToken', 'test_refresh_token'];
        yield 'expiresIn' => ['expiresIn', 3600];
        yield 'scope' => ['scope', 'read write'];
        yield 'rawData' => ['rawData', ['key' => 'value']];
    }

    public function testConstruct(): void
    {
        $user = new LarkOAuth2User();

        $this->assertNotNull($user->getCreateTime());
        $this->assertNotNull($user->getUpdateTime());
    }

    public function testBasicProperties(): void
    {
        $user = new LarkOAuth2User();

        $user->setConfig($this->config);
        $this->assertSame($this->config, $user->getConfig());

        $user->setOpenId('test_open_id');
        $this->assertSame('test_open_id', $user->getOpenId());

        $user->setUnionId('test_union_id');
        $this->assertSame('test_union_id', $user->getUnionId());

        $user->setUserId('test_user_id');
        $this->assertSame('test_user_id', $user->getUserId());

        $user->setName('Test User');
        $this->assertSame('Test User', $user->getName());

        $user->setEnName('Test User En');
        $this->assertSame('Test User En', $user->getEnName());
    }

    public function testTokenManagement(): void
    {
        $user = new LarkOAuth2User();

        $user->setAccessToken('test_access_token');
        $this->assertSame('test_access_token', $user->getAccessToken());

        $user->setRefreshToken('test_refresh_token');
        $this->assertSame('test_refresh_token', $user->getRefreshToken());

        $user->setExpiresIn(3600);
        $this->assertSame(3600, $user->getExpiresIn());
        $this->assertNotNull($user->getTokenExpiresTime());

        $user->setRefreshTokenExpiresIn(7200);
        $this->assertNotNull($user->getRefreshTokenExpiresTime());
    }

    public function testTokenExpiration(): void
    {
        $user = new LarkOAuth2User();

        // 设置已过期的令牌
        $reflectionProperty = new \ReflectionProperty($user, 'tokenExpiresTime');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($user, new \DateTimeImmutable('-1 minute'));

        $this->assertTrue($user->isTokenExpired());

        // 设置未过期的令牌
        $reflectionProperty->setValue($user, new \DateTimeImmutable('+1 hour'));
        $this->assertFalse($user->isTokenExpired());
    }

    public function testUpdateTimestamps(): void
    {
        $user = new LarkOAuth2User();
        $originalUpdatedAt = $user->getUpdateTime();

        usleep(1000);
        $user->setUpdateTime(new \DateTimeImmutable());

        $this->assertGreaterThan($originalUpdatedAt, $user->getUpdateTime());
    }

    public function testToString(): void
    {
        $user = new LarkOAuth2User();
        $user->setOpenId('test_open_id');

        $string = (string) $user;

        $this->assertStringContainsString('LarkOAuth2User', $string);
        $this->assertStringContainsString('test_open_id', $string);
    }
}
