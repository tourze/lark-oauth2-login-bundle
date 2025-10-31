<?php

declare(strict_types=1);

namespace Tourze\LarkOAuth2LoginBundle\Service;

use Knp\Menu\ItemInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2State;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2User;

#[Autoconfigure(public: true)]
readonly class AdminMenu implements MenuProviderInterface
{
    public function __construct(private LinkGeneratorInterface $linkGenerator)
    {
    }

    public function __invoke(ItemInterface $item): void
    {
        if (null === $item->getChild('飞书OAuth2登录')) {
            $item->addChild('飞书OAuth2登录');
        }

        $larkMenu = $item->getChild('飞书OAuth2登录');
        if (null === $larkMenu) {
            return;
        }

        // 配置管理
        $larkMenu
            ->addChild('应用配置')
            ->setUri($this->linkGenerator->getCurdListPage(LarkOAuth2Config::class))
            ->setAttribute('icon', 'fas fa-cogs')
        ;

        // 用户管理
        $larkMenu
            ->addChild('用户信息')
            ->setUri($this->linkGenerator->getCurdListPage(LarkOAuth2User::class))
            ->setAttribute('icon', 'fas fa-users')
        ;

        // 状态管理
        $larkMenu
            ->addChild('授权状态')
            ->setUri($this->linkGenerator->getCurdListPage(LarkOAuth2State::class))
            ->setAttribute('icon', 'fas fa-key')
        ;
    }
}
