<?php

namespace Tourze\LarkOAuth2LoginBundle\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\Routing\RouteCollection;
use Tourze\LarkOAuth2LoginBundle\Service\AttributeControllerLoader;
use Tourze\PHPUnitSymfonyKernelTest\AbstractIntegrationTestCase;

/**
 * @internal
 */
#[CoversClass(AttributeControllerLoader::class)]
#[RunTestsInSeparateProcesses]
final class AttributeControllerLoaderTest extends AbstractIntegrationTestCase
{
    protected function onSetUp(): void
    {
        // 这个测试类不需要额外的设置
    }

    public function testAutoloadRoutes(): void
    {
        $service = self::getService(AttributeControllerLoader::class);
        $collection = $service->autoload();
        $this->assertInstanceOf(RouteCollection::class, $collection);
    }

    public function testAutoload(): void
    {
        $service = self::getService(AttributeControllerLoader::class);
        $collection = $service->autoload();
        $this->assertInstanceOf(RouteCollection::class, $collection);
    }

    public function testLoad(): void
    {
        $service = self::getService(AttributeControllerLoader::class);
        $collection = $service->load('test');
        $this->assertInstanceOf(RouteCollection::class, $collection);
    }

    public function testSupports(): void
    {
        $service = self::getService(AttributeControllerLoader::class);
        $result = $service->supports('test');
        $this->assertFalse($result);
    }
}
