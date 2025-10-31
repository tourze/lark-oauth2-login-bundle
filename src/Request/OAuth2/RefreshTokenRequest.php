<?php

namespace Tourze\LarkOAuth2LoginBundle\Request\OAuth2;

use HttpClientBundle\Request\ApiRequest;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;

class RefreshTokenRequest extends ApiRequest
{
    private LarkOAuth2Config $config;

    private string $refreshToken;

    private ?string $scope = null;

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
            'grant_type' => 'refresh_token',
            'client_id' => $this->config->getAppId(),
            'client_secret' => $this->config->getAppSecret(),
            'refresh_token' => $this->refreshToken,
        ];

        if (null !== $this->scope) {
            $body['scope'] = $this->scope;
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

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): void
    {
        $this->refreshToken = $refreshToken;
    }

    public function getScope(): ?string
    {
        return $this->scope;
    }

    public function setScope(?string $scope): void
    {
        $this->scope = $scope;
    }
}
