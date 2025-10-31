<?php

namespace Tourze\LarkOAuth2LoginBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\LarkOAuth2LoginBundle\Exception\LarkOAuth2ApiException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(LarkOAuth2ApiException::class)]
final class LarkOAuth2ApiExceptionTest extends AbstractExceptionTestCase
{
    public function testInstanceCreation(): void
    {
        $exception = new LarkOAuth2ApiException('API error');

        $this->assertNotNull($exception);
        $this->assertSame('API error', $exception->getMessage());
    }
}
