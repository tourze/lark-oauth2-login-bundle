<?php

namespace Tourze\LarkOAuth2LoginBundle\Request\OAuth2;

use HttpClientBundle\Request\ApiRequest;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;

class GetAccessTokenRequest extends ApiRequest
{
    private LarkOAuth2Config $config;

    private string $code;

    private string $redirectUri;

    private ?string $codeVerifier = null;

    public function getRequestPath(): string
    {
        return 'https://open.feishu.cn/open-apis/authen/v2/oauth/token';
    }

    public function getRequestMethod(): ?string
    {
        return 'POST';
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRequestOptions(): ?array
    {
        $body = [
            'grant_type' => 'authorization_code',
            'client_id' => $this->config->getAppId(),
            'client_secret' => $this->config->getAppSecret(),
            'code' => $this->code,
            'redirect_uri' => $this->redirectUri,
        ];

        if (null !== $this->codeVerifier) {
            $body['code_verifier'] = $this->codeVerifier;
        }

        if (null !== $this->config->getScope()) {
            $body['scope'] = $this->config->getScope();
        }

        return [
            'json' => $body,
            'headers' => [
                'Content-Type' => 'application/json; charset=utf-8',
            ],
        ];
    }

    public function getConfig(): LarkOAuth2Config
    {
        return $this->config;
    }

    public function setConfig(LarkOAuth2Config $config): void
    {
        $this->config = $config;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getRedirectUri(): string
    {
        return $this->redirectUri;
    }

    public function setRedirectUri(string $redirectUri): void
    {
        $this->redirectUri = $redirectUri;
    }

    public function getCodeVerifier(): ?string
    {
        return $this->codeVerifier;
    }

    public function setCodeVerifier(?string $codeVerifier): void
    {
        $this->codeVerifier = $codeVerifier;
    }
}
