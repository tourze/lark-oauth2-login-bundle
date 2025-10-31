<?php

namespace Tourze\LarkOAuth2LoginBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2State;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(LarkOAuth2State::class)]
final class LarkOAuth2StateTest extends AbstractEntityTestCase
{
    private LarkOAuth2Config $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = new LarkOAuth2Config();
        $this->config->setAppId('test_app_id');
        $this->config->setAppSecret('test_app_secret');
    }

    protected function createEntity(): LarkOAuth2State
    {
        $entity = new LarkOAuth2State();
        $entity->setState('test_state');
        $entity->setConfig($this->config);

        return $entity;
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        $config = new LarkOAuth2Config();
        $config->setAppId('test_app_id');
        $config->setAppSecret('test_app_secret');

        yield 'sessionId' => ['sessionId', 'test_session_id'];
    }

    public function testConstruct(): void
    {
        $state = 'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6';
        $oauthState = new LarkOAuth2State();
        $oauthState->setState($state);
        $oauthState->setConfig($this->config);

        $this->assertSame($state, $oauthState->getState());
        $this->assertSame($this->config, $oauthState->getConfig());
        $this->assertNotNull($oauthState->getCreateTime());
        $this->assertNotNull($oauthState->getExpiresTime());
        $this->assertFalse($oauthState->isUsed());
        $this->assertNull($oauthState->getUsedTime());
        $this->assertNull($oauthState->getSessionId());
    }

    public function testExpiresAtIsSetCorrectly(): void
    {
        $state = 'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6';
        $oauthState = new LarkOAuth2State();
        $oauthState->setState($state);
        $oauthState->setConfig($this->config);

        $expectedExpiresAt = $oauthState->getCreateTime()->modify('+5 minutes');

        $this->assertEquals($expectedExpiresAt, $oauthState->getExpiresTime());
    }

    public function testSessionId(): void
    {
        $state = 'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6';
        $oauthState = new LarkOAuth2State();
        $oauthState->setState($state);
        $oauthState->setConfig($this->config);
        $sessionId = 'test_session_id';

        $oauthState->setSessionId($sessionId);

        $this->assertSame($sessionId, $oauthState->getSessionId());

        $oauthState->setSessionId(null);
        $this->assertNull($oauthState->getSessionId());
    }

    public function testMarkAsUsed(): void
    {
        $state = 'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6';
        $oauthState = new LarkOAuth2State();
        $oauthState->setState($state);
        $oauthState->setConfig($this->config);

        $this->assertFalse($oauthState->isUsed());
        $this->assertNull($oauthState->getUsedTime());

        $oauthState->markAsUsed();

        $this->assertTrue($oauthState->isUsed());
        $this->assertNotNull($oauthState->getUsedTime());
    }

    public function testIsValid(): void
    {
        $state = 'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6';
        $oauthState = new LarkOAuth2State();
        $oauthState->setState($state);
        $oauthState->setConfig($this->config);

        // 新创建的状态应该是有效的
        $this->assertTrue($oauthState->isValid());

        // 标记为已使用后应该无效
        $oauthState->markAsUsed();
        $this->assertFalse($oauthState->isValid());
    }

    public function testIsExpired(): void
    {
        $state = 'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6';
        $oauthState = new LarkOAuth2State();
        $oauthState->setState($state);
        $oauthState->setConfig($this->config);

        // 新创建的状态不应该过期
        $this->assertFalse($oauthState->isExpired());

        // 测试过期状态需要模拟时间，这里只能测试逻辑
        $reflectionProperty = new \ReflectionProperty($oauthState, 'expiresTime');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($oauthState, new \DateTimeImmutable('-1 minute'));

        $this->assertTrue($oauthState->isExpired());
        $this->assertFalse($oauthState->isValid());
    }

    public function testToString(): void
    {
        $state = 'a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6';
        $oauthState = new LarkOAuth2State();
        $oauthState->setState($state);
        $oauthState->setConfig($this->config);

        $string = (string) $oauthState;

        $this->assertStringContainsString('LarkOAuth2State', $string);
        $this->assertStringContainsString($state, $string);
    }
}
