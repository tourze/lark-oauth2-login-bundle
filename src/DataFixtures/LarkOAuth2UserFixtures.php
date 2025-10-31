<?php

namespace Tourze\LarkOAuth2LoginBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2User;

#[When(env: 'test')]
class LarkOAuth2UserFixtures extends Fixture implements DependentFixtureInterface
{
    public const LARK_USER_VALID_TOKEN_REFERENCE = 'lark-user-valid-token';
    public const LARK_USER_EXPIRED_TOKEN_REFERENCE = 'lark-user-expired-token';

    public function load(ObjectManager $manager): void
    {
        $config = $this->getReference(LarkOAuth2ConfigFixtures::LARK_CONFIG_REFERENCE, LarkOAuth2Config::class);

        $userWithValidToken = new LarkOAuth2User();
        $userWithValidToken->setConfig($config);
        $userWithValidToken->setOpenId('ou_test_open_id_123456');
        $userWithValidToken->setUnionId('on_test_union_id_123456');
        $userWithValidToken->setUserId('test_user_id_123');
        $userWithValidToken->setName('张三');
        $userWithValidToken->setEnName('Zhang San');
        $userWithValidToken->setEmail('zhangsan@test.local');
        $userWithValidToken->setMobile('+86138****1234');
        $userWithValidToken->setTenantKey('test_tenant_key');
        $userWithValidToken->setEmployeeNo('EMP001');
        $userWithValidToken->setAccessToken('at_test_access_token_123456789');
        $userWithValidToken->setRefreshToken('rt_test_refresh_token_123456789');
        $userWithValidToken->setExpiresIn(7200);
        $userWithValidToken->setScope('contact:user.email:readonly contact:user.base:readonly');
        $userWithValidToken->setRawData([
            'name' => '张三',
            'en_name' => 'Zhang San',
            'avatar_url' => 'https://test.local/avatar.jpg',
            'email' => 'zhangsan@test.local',
        ]);
        $userWithValidToken->setRefreshTokenExpiresIn(2592000);

        $manager->persist($userWithValidToken);
        $this->addReference(self::LARK_USER_VALID_TOKEN_REFERENCE, $userWithValidToken);

        $userWithExpiredToken = new LarkOAuth2User();
        $userWithExpiredToken->setConfig($config);
        $userWithExpiredToken->setOpenId('ou_test_open_id_expired');
        $userWithExpiredToken->setUnionId('on_test_union_id_expired');
        $userWithExpiredToken->setUserId('test_user_id_expired');
        $userWithExpiredToken->setName('李四');
        $userWithExpiredToken->setEnName('Li Si');
        $userWithExpiredToken->setEmail('lisi@test.local');
        $userWithExpiredToken->setAccessToken('at_expired_access_token');
        $userWithExpiredToken->setExpiresIn(-3600);

        $manager->persist($userWithExpiredToken);
        $this->addReference(self::LARK_USER_EXPIRED_TOKEN_REFERENCE, $userWithExpiredToken);

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            LarkOAuth2ConfigFixtures::class,
        ];
    }
}
