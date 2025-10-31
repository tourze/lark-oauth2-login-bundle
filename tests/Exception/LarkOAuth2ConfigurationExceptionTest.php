<?php

namespace Tourze\LarkOAuth2LoginBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\LarkOAuth2LoginBundle\Exception\LarkOAuth2ConfigurationException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(LarkOAuth2ConfigurationException::class)]
final class LarkOAuth2ConfigurationExceptionTest extends AbstractExceptionTestCase
{
    public function testInstanceCreation(): void
    {
        $exception = new LarkOAuth2ConfigurationException('Configuration error');

        $this->assertNotNull($exception);
        $this->assertSame('Configuration error', $exception->getMessage());
    }
}
