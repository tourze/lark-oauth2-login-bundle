<?php

namespace Tourze\LarkOAuth2LoginBundle\Request\OAuth2;

use HttpClientBundle\Request\ApiRequest;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;

class GetUserInfoRequest extends ApiRequest
{
    private LarkOAuth2Config $config;

    private string $accessToken;

    public function getRequestPath(): string
    {
        return 'https://open.feishu.cn/open-apis/authen/v1/user_info';
    }

    public function getRequestMethod(): ?string
    {
        return 'GET';
    }

    /**
     * @return array<string, mixed>|null
     */
    public function getRequestOptions(): ?array
    {
        return [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->accessToken,
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

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): void
    {
        $this->accessToken = $accessToken;
    }
}
