<?php

namespace Tourze\LarkOAuth2LoginBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\LarkOAuth2LoginBundle\Exception\LarkOAuth2RuntimeException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(LarkOAuth2RuntimeException::class)]
final class LarkOAuth2ExceptionTest extends AbstractExceptionTestCase
{
    public function testInstanceCreation(): void
    {
        $exception = new LarkOAuth2RuntimeException('OAuth2 error');

        $this->assertNotNull($exception);
        $this->assertSame('OAuth2 error', $exception->getMessage());
    }
}
