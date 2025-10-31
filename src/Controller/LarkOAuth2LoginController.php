<?php

namespace Tourze\LarkOAuth2LoginBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Tourze\LarkOAuth2LoginBundle\Service\LarkOAuth2Service;

final class LarkOAuth2LoginController extends AbstractController
{
    public function __construct(
        private readonly LarkOAuth2Service $oauth2Service,
    ) {
    }

    #[Route(path: '/lark-oauth2/login', name: 'lark_oauth2_login', methods: ['GET'])]
    public function __invoke(Request $request): RedirectResponse
    {
        $sessionId = $request->getSession()->getId();

        // 支持PKCE流程（可选）
        $codeChallenge = $request->query->get('code_challenge');
        $codeChallengeMethod = $request->query->get('code_challenge_method');

        $authUrl = $this->oauth2Service->generateAuthorizationUrl($sessionId, null !== $codeChallenge ? (string) $codeChallenge : null, null !== $codeChallengeMethod ? (string) $codeChallengeMethod : null);

        return new RedirectResponse($authUrl);
    }
}
