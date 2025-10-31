<?php

namespace Tourze\LarkOAuth2LoginBundle\Tests\Request\OAuth2;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\LarkOAuth2LoginBundle\Request\OAuth2\RefreshTokenRequest;

/**
 * @internal
 */
#[CoversClass(RefreshTokenRequest::class)]
final class RefreshTokenRequestTest extends RequestTestCase
{
    public function testInstanceCreation(): void
    {
        $request = new RefreshTokenRequest();

        $this->assertNotNull($request);
    }
}
