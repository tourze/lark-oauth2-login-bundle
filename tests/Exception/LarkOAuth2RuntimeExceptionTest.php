<?php

declare(strict_types=1);

namespace Tourze\LarkOAuth2LoginBundle\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\LarkOAuth2LoginBundle\Exception\LarkOAuth2Exception;
use Tourze\LarkOAuth2LoginBundle\Exception\LarkOAuth2RuntimeException;
use Tourze\PHPUnitBase\AbstractExceptionTestCase;

/**
 * @internal
 */
#[CoversClass(LarkOAuth2RuntimeException::class)]
final class LarkOAuth2RuntimeExceptionTest extends AbstractExceptionTestCase
{
    public function testInstanceCreation(): void
    {
        $exception = new LarkOAuth2RuntimeException('Runtime error');

        $this->assertNotNull($exception);
        $this->assertSame('Runtime error', $exception->getMessage());
    }

    public function testInheritsFromLarkOAuth2Exception(): void
    {
        $exception = new LarkOAuth2RuntimeException('Runtime error');

        $this->assertInstanceOf(LarkOAuth2Exception::class, $exception);
    }

    public function testWithCodeAndPrevious(): void
    {
        $previous = new \Exception('Previous exception');
        $exception = new LarkOAuth2RuntimeException('Runtime error', 123, $previous);

        $this->assertSame('Runtime error', $exception->getMessage());
        $this->assertSame(123, $exception->getCode());
        $this->assertSame($previous, $exception->getPrevious());
    }

    public function testWithContext(): void
    {
        $context = ['key' => 'value', 'user_id' => 123];
        $exception = new LarkOAuth2RuntimeException('Runtime error', 0, null, $context);

        $this->assertSame($context, $exception->getContext());
    }
}
