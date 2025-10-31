<?php

namespace Tourze\LarkOAuth2LoginBundle\Factory;

use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2User;

class LarkOAuth2UserFactory
{
    public function create(string $openId, LarkOAuth2Config $config): LarkOAuth2User
    {
        $user = new LarkOAuth2User();
        $user->setOpenId($openId);
        $user->setConfig($config);

        return $user;
    }

    /**
     * @param array<string, mixed> $userData
     */
    public function updateFromUserData(LarkOAuth2User $user, array $userData): void
    {
        $this->updateUserIdentifiers($user, $userData);
        $this->updateUserProfile($user, $userData);
        $this->updateUserContact($user, $userData);
        $this->updateTokenInfo($user, $userData);
        $user->setRawData($userData);
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function updateUserIdentifiers(LarkOAuth2User $user, array $userData): void
    {
        $this->setStringField($user, $userData, 'union_id', fn($v) => $user->setUnionId($v));
        $this->setStringField($user, $userData, 'user_id', fn($v) => $user->setUserId($v));
        $this->setStringField($user, $userData, 'tenant_key', fn($v) => $user->setTenantKey($v));
        $this->setStringField($user, $userData, 'employee_no', fn($v) => $user->setEmployeeNo($v));
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function updateUserProfile(LarkOAuth2User $user, array $userData): void
    {
        $this->setStringField($user, $userData, 'name', fn($v) => $user->setName($v));
        $this->setStringField($user, $userData, 'en_name', fn($v) => $user->setEnName($v));
        $this->setStringField($user, $userData, 'avatar_url', fn($v) => $user->setAvatarUrl($v));
        $this->setStringField($user, $userData, 'avatar_thumb', fn($v) => $user->setAvatarThumb($v));
        $this->setStringField($user, $userData, 'avatar_middle', fn($v) => $user->setAvatarMiddle($v));
        $this->setStringField($user, $userData, 'avatar_big', fn($v) => $user->setAvatarBig($v));
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function updateUserContact(LarkOAuth2User $user, array $userData): void
    {
        $this->setStringField($user, $userData, 'email', fn($v) => $user->setEmail($v));
        $this->setStringField($user, $userData, 'mobile', fn($v) => $user->setMobile($v));
    }

    /**
     * @param array<string, mixed> $userData
     */
    private function updateTokenInfo(LarkOAuth2User $user, array $userData): void
    {
        $this->setStringField($user, $userData, 'refresh_token', fn($v) => $user->setRefreshToken($v));
        $this->setStringField($user, $userData, 'scope', fn($v) => $user->setScope($v));
        $this->setStringField($user, $userData, 'access_token', fn($v) => $user->setAccessToken($v));
        $this->setIntField($user, $userData, 'expires_in', fn($v) => $user->setExpiresIn($v));
        $this->setIntField($user, $userData, 'refresh_token_expires_in', fn($v) => $user->setRefreshTokenExpiresIn($v));
    }

    /**
     * @param array<string, mixed> $userData
     * @param callable(string): void $setter
     */
    private function setStringField(LarkOAuth2User $user, array $userData, string $key, callable $setter): void
    {
        if (isset($userData[$key]) && is_string($userData[$key])) {
            $setter($userData[$key]);
        }
    }

    /**
     * @param array<string, mixed> $userData
     * @param callable(int): void $setter
     */
    private function setIntField(LarkOAuth2User $user, array $userData, string $key, callable $setter): void
    {
        if (isset($userData[$key]) && is_int($userData[$key])) {
            $setter($userData[$key]);
        }
    }
}
