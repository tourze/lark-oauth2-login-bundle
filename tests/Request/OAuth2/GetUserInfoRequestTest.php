<?php

namespace Tourze\LarkOAuth2LoginBundle\Tests\Request\OAuth2;

use HttpClientBundle\Tests\Request\RequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Tourze\LarkOAuth2LoginBundle\Request\OAuth2\GetUserInfoRequest;

/**
 * @internal
 */
#[CoversClass(GetUserInfoRequest::class)]
final class GetUserInfoRequestTest extends RequestTestCase
{
    public function testInstanceCreation(): void
    {
        $request = new GetUserInfoRequest();

        $this->assertNotNull($request);
    }
}
