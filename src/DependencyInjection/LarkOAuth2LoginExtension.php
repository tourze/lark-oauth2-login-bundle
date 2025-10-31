<?php

namespace Tourze\LarkOAuth2LoginBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tourze\SymfonyDependencyServiceLoader\AutoExtension;

class LarkOAuth2LoginExtension extends AutoExtension
{
    protected function getConfigDir(): string
    {
        return __DIR__ . '/../Resources/config';
    }
}
