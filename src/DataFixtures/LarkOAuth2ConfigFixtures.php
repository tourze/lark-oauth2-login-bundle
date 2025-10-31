<?php

namespace Tourze\LarkOAuth2LoginBundle\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\When;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;

#[When(env: 'test')]
class LarkOAuth2ConfigFixtures extends Fixture
{
    public const LARK_CONFIG_REFERENCE = 'lark-config';
    public const LARK_CONFIG_DISABLED_REFERENCE = 'lark-config-disabled';

    public function load(ObjectManager $manager): void
    {
        $config = new LarkOAuth2Config();
        $config->setAppId('cli_a1b2c3d4e5f6g7h8');
        $config->setAppSecret('test_app_secret_12345');
        $config->setName('测试飞书应用');
        $config->setScope('contact:user.email:readonly contact:user.base:readonly');
        $config->setRemark('用于测试的飞书OAuth2配置');
        $config->setValid(true);

        $manager->persist($config);
        $this->addReference(self::LARK_CONFIG_REFERENCE, $config);

        $disabledConfig = new LarkOAuth2Config();
        $disabledConfig->setAppId('cli_disabled_app_id_test');
        $disabledConfig->setAppSecret('disabled_app_secret_test');
        $disabledConfig->setName('已禁用的测试应用');
        $disabledConfig->setScope('contact:user.base:readonly');
        $disabledConfig->setRemark('已禁用的测试配置');
        $disabledConfig->setValid(false);

        $manager->persist($disabledConfig);
        $this->addReference(self::LARK_CONFIG_DISABLED_REFERENCE, $disabledConfig);

        $manager->flush();
    }
}
