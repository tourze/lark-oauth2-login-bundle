<?php

namespace Tourze\LarkOAuth2LoginBundle\Tests\Entity;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;
use Tourze\PHPUnitDoctrineEntity\AbstractEntityTestCase;

/**
 * @internal
 */
#[CoversClass(LarkOAuth2Config::class)]
final class LarkOAuth2ConfigTest extends AbstractEntityTestCase
{
    protected function createEntity(): LarkOAuth2Config
    {
        return new LarkOAuth2Config();
    }

    /**
     * @return iterable<string, array{string, mixed}>
     */
    public static function propertiesProvider(): iterable
    {
        yield 'appId' => ['appId', 'test_app_id'];
        yield 'appSecret' => ['appSecret', 'test_app_secret'];
        yield 'name' => ['name', 'Test Config'];
        yield 'scope' => ['scope', 'read write'];
        yield 'remark' => ['remark', 'Test remark'];
        yield 'valid' => ['valid', false];
    }

    public function testConstruct(): void
    {
        $config = new LarkOAuth2Config();

        $this->assertNotNull($config->getCreateTime());
        $this->assertNotNull($config->getUpdateTime());
        $this->assertTrue($config->isValid());
    }

    public function testAppId(): void
    {
        $config = new LarkOAuth2Config();
        $appId = 'test_app_id';

        $config->setAppId($appId);

        $this->assertSame($appId, $config->getAppId());
    }

    public function testAppSecret(): void
    {
        $config = new LarkOAuth2Config();
        $appSecret = 'test_app_secret';

        $config->setAppSecret($appSecret);

        $this->assertSame($appSecret, $config->getAppSecret());
    }

    public function testName(): void
    {
        $config = new LarkOAuth2Config();
        $name = 'Test Config';

        $config->setName($name);

        $this->assertSame($name, $config->getName());

        $config->setName(null);
        $this->assertNull($config->getName());
    }

    public function testScope(): void
    {
        $config = new LarkOAuth2Config();
        $scope = 'read write';

        $config->setScope($scope);

        $this->assertSame($scope, $config->getScope());

        $config->setScope(null);
        $this->assertNull($config->getScope());
    }

    public function testRemark(): void
    {
        $config = new LarkOAuth2Config();
        $remark = 'Test remark';

        $config->setRemark($remark);

        $this->assertSame($remark, $config->getRemark());

        $config->setRemark(null);
        $this->assertNull($config->getRemark());
    }

    public function testIsValid(): void
    {
        $config = new LarkOAuth2Config();

        $this->assertTrue($config->isValid());

        $config->setValid(false);

        $this->assertFalse($config->isValid());
    }

    public function testUpdateTimestamps(): void
    {
        $config = new LarkOAuth2Config();
        $originalUpdatedAt = $config->getUpdateTime();

        usleep(1000); // 等待微秒，确保时间不同
        $config->setUpdateTime(new \DateTimeImmutable());

        $this->assertGreaterThan($originalUpdatedAt, $config->getUpdateTime());
    }

    public function testToString(): void
    {
        $config = new LarkOAuth2Config();
        $config->setAppId('test_app_id');

        $string = (string) $config;

        $this->assertStringContainsString('LarkOAuth2Config', $string);
        $this->assertStringContainsString('test_app_id', $string);
    }
}
