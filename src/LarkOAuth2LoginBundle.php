<?php

namespace Tourze\LarkOAuth2LoginBundle;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use HttpClientBundle\HttpClientBundle;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tourze\BundleDependency\BundleDependencyInterface;
use Tourze\RoutingAutoLoaderBundle\RoutingAutoLoaderBundle;

class LarkOAuth2LoginBundle extends Bundle implements BundleDependencyInterface
{
    public static function getBundleDependencies(): array
    {
        return [
            DoctrineBundle::class => ['all' => true],
            HttpClientBundle::class => ['all' => true],
            RoutingAutoLoaderBundle::class => ['all' => true],
        ];
    }
}
