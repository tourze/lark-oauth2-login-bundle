<?php

namespace Tourze\LarkOAuth2LoginBundle\Tests\Controller;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Tourze\LarkOAuth2LoginBundle\Controller\LarkOAuth2LoginController;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;
use Tourze\LarkOAuth2LoginBundle\Repository\LarkOAuth2ConfigRepository;
use Tourze\PHPUnitSymfonyWebTest\AbstractWebTestCase;

/**
 * @internal
 */
#[CoversClass(LarkOAuth2LoginController::class)]
#[RunTestsInSeparateProcesses]
final class LarkOAuth2LoginControllerTest extends AbstractWebTestCase
{
    private function createTestConfig(?string $appId = null): LarkOAuth2Config
    {
        $config = new LarkOAuth2Config();
        $config->setAppId($appId ?? 'cli_a1b2c3d4e5f6g7h8_' . uniqid());
        $config->setAppSecret('test_app_secret_12345_' . uniqid());
        $config->setName('测试飞书应用 ' . uniqid());
        $config->setScope('contact:user.email:readonly contact:user.base:readonly');
        $config->setRemark('用于测试的飞书OAuth2配置');
        $config->setValid(true);

        $configRepository = self::getService(LarkOAuth2ConfigRepository::class);
        self::assertInstanceOf(LarkOAuth2ConfigRepository::class, $configRepository);
        $configRepository->save($config);

        return $config;
    }

    public function testGetLoginRedirect(): void
    {
        $client = self::createClientWithDatabase();
        $this->createTestConfig();
        $client->request('GET', '/lark-oauth2/login');

        $this->assertTrue($client->getResponse()->isRedirect());
        $this->assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        $location = $client->getResponse()->headers->get('Location');
        $this->assertStringContainsString('https://accounts.feishu.cn/open-apis/authen/v1/authorize', $location ?? '');
    }

    public function testGetLoginWithPKCE(): void
    {
        $client = self::createClientWithDatabase();
        $this->createTestConfig();
        $client->request('GET', '/lark-oauth2/login', [
            'code_challenge' => 'test_challenge',
            'code_challenge_method' => 'S256',
        ]);

        $this->assertTrue($client->getResponse()->isRedirect());
        $this->assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());

        $location = $client->getResponse()->headers->get('Location');
        $locationStr = $location ?? '';
        $this->assertStringContainsString('https://accounts.feishu.cn/open-apis/authen/v1/authorize', $locationStr);
        $this->assertStringContainsString('code_challenge=test_challenge', $locationStr);
        $this->assertStringContainsString('code_challenge_method=S256', $locationStr);
    }

    public function testPostMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('POST', '/lark-oauth2/login');
    }

    public function testPutMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PUT', '/lark-oauth2/login');
    }

    public function testDeleteMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('DELETE', '/lark-oauth2/login');
    }

    public function testPatchMethodNotAllowed(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('PATCH', '/lark-oauth2/login');
    }

    public function testHeadMethod(): void
    {
        $client = self::createClientWithDatabase();
        $this->createTestConfig();
        $client->request('HEAD', '/lark-oauth2/login');

        $this->assertTrue($client->getResponse()->isRedirect());
        $this->assertSame(Response::HTTP_FOUND, $client->getResponse()->getStatusCode());
    }

    public function testOptionsMethod(): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request('OPTIONS', '/lark-oauth2/login');
    }

    #[DataProvider('provideNotAllowedMethods')]
    public function testMethodNotAllowed(string $method): void
    {
        $client = self::createClientWithDatabase();
        $client->catchExceptions(false);

        $this->expectException(MethodNotAllowedHttpException::class);
        $client->request($method, '/lark-oauth2/login');
    }
}
