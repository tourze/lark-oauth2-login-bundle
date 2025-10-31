<?php

namespace Tourze\LarkOAuth2LoginBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\LarkOAuth2LoginBundle\Controller\LarkOAuth2CallbackController;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;

/**
 * @internal
 */
#[CoversClass(LarkOAuth2CallbackController::class)]
#[RunTestsInSeparateProcesses]
final class LarkOAuth2CallbackControllerTest extends AbstractWebTestCase
{
    public function testIndexWithoutAuthentication(): void
    {
        $client = self::createClientWithDatabase();
        $client->request('GET', '/lark-oauth2/callback');

        $this->assertSame(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();
        $this->assertStringContainsString('Invalid callback parameters', false !== $content ? $content : '');
    }

    public function testGetCallbackWithoutParameters(): void
    {
        $client = self::createClientWithDatabase();
        $client->request('GET', '/lark-oauth2/callback');

        $this->assertSame(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();
        $this->assertStringContainsString('Invalid callback parameters', false !== $content ? $content : '');
    }

    public function testGetCallbackWithError(): void
    {
        $client = self::createClientWithDatabase();
        $client->request('GET', '/lark-oauth2/callback', [
            'error' => 'access_denied',
        ]);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();
        $this->assertStringContainsString('OAuth2 Error: access_denied', false !== $content ? $content : '');
    }

    #[DataProvider('invalidParametersProvider')]
    public function testGetCallbackWithInvalidParameters(string $code, string $state, string $expectedMessage): void
    {
        $client = self::createClientWithDatabase();
        $client->request('GET', '/lark-oauth2/callback', [
            'code' => $code,
            'state' => $state,
        ]);

        $this->assertSame(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getContent();
        $this->assertStringContainsString($expectedMessage, false !== $content ? $content : '');
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function invalidParametersProvider(): array
    {
        return [
            'invalid_code_format' => ['invalid@code', 'a1b2c3d4e5f6789012345678901234ab', 'Malformed callback parameters'],
            'invalid_state_format' => ['validcode123', 'invalid-state', 'Malformed callback parameters'],
            'empty_code' => ['', 'a1b2c3d4e5f6789012345678901234ab', 'Malformed callback parameters'],
            'empty_state' => ['validcode123', '', 'Malformed callback parameters'],
        ];
    }

    public function testPostMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/lark-oauth2/callback');
    }

    public function testPutMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/lark-oauth2/callback');
    }

    public function testDeleteMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/lark-oauth2/callback');
    }

    public function testPatchMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/lark-oauth2/callback');
    }

    public function testHeadMethod(): void
    {
        $client = self::createClientWithDatabase();
        $client->request('HEAD', '/lark-oauth2/callback');

        $this->assertSame(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
    }

    public function testOptionsMethod(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/lark-oauth2/callback');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/lark-oauth2/callback');
    }
}
