<?php

namespace Tourze\LarkOAuth2LoginBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\LarkOAuth2LoginBundle\Exception\InvalidArgumentException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(InvalidArgumentException::class)]
final class InvalidArgumentExceptionTest extends AbstractExceptionTestCase
{
    public function testInstanceCreation(): void
    {
        $exception = new InvalidArgumentException('Test message');

        $this->assertNotNull($exception);
        $this->assertSame('Test message', $exception->getMessage());
    }
}
