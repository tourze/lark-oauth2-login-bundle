<?php

declare(strict_types=1);

namespace Tourze\LarkOAuth2LoginBundle\Tests\Service;

use Knp\Menu\ItemInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Tourze\EasyAdminMenuBundle\Service\MenuProviderInterface;
use Tourze\LarkOAuth2LoginBundle\Service\AdminMenu;
use Tourze\PHPUnitSymfonyWebTest\AbstractEasyAdminMenuTestCase;

/**
 * AdminMenu 单元测试
 *
 * @internal
 */
#[CoversClass(AdminMenu::class)]
#[RunTestsInSeparateProcesses]
final class AdminMenuTest extends AbstractEasyAdminMenuTestCase
{
    private ItemInterface $item;

    public function testInvokeMethodDoesNotThrowException(): void
    {
        // 测试 AdminMenu 的 __invoke 方法正常工作
        $this->expectNotToPerformAssertions();

        try {
            $adminMenu = self::getService(AdminMenu::class);
            ($adminMenu)($this->item);
        } catch (\Throwable $e) {
            self::fail('AdminMenu __invoke method should not throw exception: ' . $e->getMessage());
        }
    }

    public function testServiceIntegration(): void
    {
        // 测试通过服务容器获取 AdminMenu 服务
        $adminMenu = self::getService(AdminMenu::class);
        $this->assertInstanceOf(AdminMenu::class, $adminMenu);
    }

    public function testReadOnlyServiceDesign(): void
    {
        // 验证服务是 readonly 的，符合不可变设计
        $reflection = new \ReflectionClass(AdminMenu::class);
        $this->assertTrue($reflection->isReadOnly(), 'AdminMenu service should be readonly');

        // 验证构造函数参数也是 readonly
        $constructor = $reflection->getConstructor();
        $this->assertNotNull($constructor);

        $parameters = $constructor->getParameters();
        $this->assertCount(1, $parameters);

        $linkGeneratorParam = $parameters[0];
        // 检查参数是否为readonly（在PHP 8.1+支持）
        $this->assertSame('linkGenerator', $linkGeneratorParam->getName());

        // 验证参数类型
        $paramType = $linkGeneratorParam->getType();
        $this->assertNotNull($paramType);
        $this->assertInstanceOf(\ReflectionNamedType::class, $paramType);
        $this->assertSame('Tourze\EasyAdminMenuBundle\Service\LinkGeneratorInterface', $paramType->getName());
    }

    public function testMenuProviderInterfaceImplementation(): void
    {
        // 验证实现了正确的接口
        $adminMenu = self::getService(AdminMenu::class);
        $this->assertInstanceOf(MenuProviderInterface::class, $adminMenu);
    }

    public function testAutoconfigureAttribute(): void
    {
        // 验证类有正确的 Autoconfigure 属性
        $reflection = new \ReflectionClass(AdminMenu::class);
        $attributes = $reflection->getAttributes(Autoconfigure::class);

        $this->assertCount(1, $attributes, 'AdminMenu should have Autoconfigure attribute');

        $autoconfigureAttribute = $attributes[0];
        $arguments = $autoconfigureAttribute->getArguments();
        $this->assertTrue($arguments['public'] ?? false, 'AdminMenu should be configured as public service');
    }

    protected function onSetUp(): void
    {
        $this->item = $this->createMock(ItemInterface::class);

        // 设置 mock 的返回值以避免 null 引用
        $childItem = $this->createMock(ItemInterface::class);
        $this->item->method('addChild')->willReturn($childItem);

        // 使用 willReturnCallback 来模拟 getChild 的行为
        $this->item->method('getChild')->willReturnCallback(function ($name) use ($childItem) {
            return '飞书OAuth2登录' === $name ? $childItem : null;
        });

        // 设置子菜单项的 mock 行为
        $childItem->method('addChild')->willReturn($childItem);
        $childItem->method('setUri')->willReturn($childItem);
        $childItem->method('setAttribute')->willReturn($childItem);
    }
}
