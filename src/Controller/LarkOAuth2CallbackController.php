<?php

namespace Tourze\LarkOAuth2LoginBundle\Controller;

use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\LarkOAuth2LoginBundle\Service\LarkOAuth2Service;

#[WithMonologChannel(channel: 'lark_o_auth2_login')]
final class LarkOAuth2CallbackController extends AbstractController
{
    public function __construct(
        private readonly LarkOAuth2Service $oauth2Service,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    #[Route(path: '/lark-oauth2/callback', name: 'lark_oauth2_callback', methods: ['GET'])]
    public function __invoke(Request $request): Response
    {
        $code = $request->query->get('code');
        $state = $request->query->get('state');
        $error = $request->query->get('error');

        // 检查OAuth错误响应
        if (null !== $error) {
            $this->logger?->warning('Lark OAuth2 error response', [
                'error' => $error,
                'ip' => $request->getClientIp(),
            ]);

            return new Response(sprintf('OAuth2 Error: %s', $error), Response::HTTP_BAD_REQUEST);
        }

        // 验证必要参数
        if (null === $code || null === $state) {
            $this->logger?->warning('Invalid Lark OAuth2 callback parameters', [
                'has_code' => null !== $code && '' !== $code,
                'has_state' => null !== $state && '' !== $state,
                'ip' => $request->getClientIp(),
            ]);

            return new Response('Invalid callback parameters', Response::HTTP_BAD_REQUEST);
        }

        // 验证参数格式
        if (1 !== preg_match('/^[a-zA-Z0-9_-]+$/', (string) $code) || 1 !== preg_match('/^[a-fA-F0-9]{32}$/', (string) $state)) {
            $this->logger?->warning('Malformed Lark OAuth2 callback parameters', [
                'ip' => $request->getClientIp(),
            ]);

            return new Response('Malformed callback parameters', Response::HTTP_BAD_REQUEST);
        }

        try {
            $user = $this->oauth2Service->handleCallback((string) $code, (string) $state);

            $this->logger?->info('Lark OAuth2 login successful', [
                'open_id' => $user->getOpenId(),
                'name' => $user->getName(),
                'ip' => $request->getClientIp(),
            ]);

            // 这里可以与应用的用户系统集成
            // 例如：创建或更新本地用户、设置认证等

            return new Response(sprintf('Successfully logged in as %s', $user->getName() ?? $user->getOpenId()));
        } catch (\Exception $e) {
            $this->logger?->error('Lark OAuth2 login failed', [
                'error' => $e->getMessage(),
                'ip' => $request->getClientIp(),
                'code_prefix' => substr((string) $code, 0, 8) . '...',
                'state' => $state,
            ]);

            return new Response('Login failed: Authentication error', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
