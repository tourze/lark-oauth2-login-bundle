<?php

declare(strict_types=1);

namespace Tourze\LarkOAuth2LoginBundle\Tests;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\LarkOAuth2LoginBundle\LarkOAuth2LoginBundle;
use Tourze\PHPUnitSymfonyKernelTest\AbstractBundleTestCase;

/**
 * @internal
 */
#[CoversClass(LarkOAuth2LoginBundle::class)]
#[RunTestsInSeparateProcesses]
final class LarkOAuth2LoginBundleTest extends AbstractBundleTestCase
{
}
