<?php

namespace Tourze\LarkOAuth2LoginBundle\Tests\Request\OAuth2;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\LarkOAuth2LoginBundle\Request\OAuth2\GetAccessTokenRequest;

/**
 * @internal
 */
#[CoversClass(GetAccessTokenRequest::class)]
final class GetAccessTokenRequestTest extends RequestTestCase
{
    public function testInstanceCreation(): void
    {
        $request = new GetAccessTokenRequest();

        $this->assertNotNull($request);
    }
}
