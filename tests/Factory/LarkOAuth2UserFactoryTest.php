<?php

namespace Tourze\LarkOAuth2LoginBundle\Tests\Factory;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2User;
use Tourze\LarkOAuth2LoginBundle\Factory\LarkOAuth2UserFactory;

/**
 * @internal
 */
#[CoversClass(LarkOAuth2UserFactory::class)]
final class LarkOAuth2UserFactoryTest extends TestCase
{
    private LarkOAuth2UserFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new LarkOAuth2UserFactory();
    }

    public function testCreate(): void
    {
        $config = new LarkOAuth2Config();
        $openId = 'test_open_id';

        $user = $this->factory->create($openId, $config);

        $this->assertInstanceOf(LarkOAuth2User::class, $user);
        $this->assertEquals($openId, $user->getOpenId());
        $this->assertEquals($config, $user->getConfig());
    }

    public function testUpdateFromUserData(): void
    {
        $config = new LarkOAuth2Config();
        $user = $this->factory->create('test_open_id', $config);

        $userData = [
            'union_id' => 'test_union_id',
            'user_id' => 'test_user_id',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'access_token' => 'test_token',
        ];

        $this->factory->updateFromUserData($user, $userData);

        $this->assertEquals('test_union_id', $user->getUnionId());
        $this->assertEquals('test_user_id', $user->getUserId());
        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('test_token', $user->getAccessToken());
        $this->assertEquals($userData, $user->getRawData());
    }

    public function testUpdateFromUserDataWithPartialData(): void
    {
        $config = new LarkOAuth2Config();
        $user = $this->factory->create('test_open_id', $config);

        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com',
        ];

        $this->factory->updateFromUserData($user, $userData);

        $this->assertEquals('Test User', $user->getName());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertNull($user->getUnionId());
        $this->assertNull($user->getUserId());
        $this->assertEquals($userData, $user->getRawData());
    }

    public function testUpdateFromUserDataWithEmptyData(): void
    {
        $config = new LarkOAuth2Config();
        $user = $this->factory->create('test_open_id', $config);

        $userData = [];

        $this->factory->updateFromUserData($user, $userData);

        $this->assertNull($user->getUnionId());
        $this->assertNull($user->getUserId());
        $this->assertNull($user->getName());
        $this->assertNull($user->getEmail());
        $this->assertEquals($userData, $user->getRawData());
    }
}
