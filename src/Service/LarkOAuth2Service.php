<?php

namespace Tourze\LarkOAuth2LoginBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use HttpClientBundle\Client\ApiClient;
use HttpClientBundle\Request\RequestInterface;
use HttpClientBundle\Service\SmartHttpClient;
use Monolog\Attribute\WithMonologChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Tourze\DoctrineAsyncInsertBundle\Service\AsyncInsertService;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2State;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2User;
use Tourze\LarkOAuth2LoginBundle\Exception\LarkOAuth2ApiException;
use Tourze\LarkOAuth2LoginBundle\Exception\LarkOAuth2ConfigurationException;
use Tourze\LarkOAuth2LoginBundle\Exception\LarkOAuth2RuntimeException;
use Tourze\LarkOAuth2LoginBundle\Repository\LarkOAuth2ConfigRepository;
use Tourze\LarkOAuth2LoginBundle\Repository\LarkOAuth2StateRepository;
use Tourze\LarkOAuth2LoginBundle\Repository\LarkOAuth2UserRepository;
use Tourze\LarkOAuth2LoginBundle\Request\OAuth2\GetAccessTokenRequest;
use Tourze\LarkOAuth2LoginBundle\Request\OAuth2\GetUserInfoRequest;
use Tourze\LarkOAuth2LoginBundle\Request\OAuth2\RefreshTokenRequest;

#[WithMonologChannel(channel: 'lark_o_auth2_login')]
class LarkOAuth2Service extends ApiClient
{
    private const AUTHORIZE_URL = 'https://accounts.feishu.cn/open-apis/authen/v1/authorize';

    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly SmartHttpClient $httpClient,
        private readonly LarkOAuth2ConfigRepository $configRepository,
        private readonly LarkOAuth2StateRepository $stateRepository,
        private readonly LarkOAuth2UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly LockFactory $lockFactory,
        private readonly CacheInterface $cache,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly AsyncInsertService $asyncInsertService,
    ) {
    }

    protected function getLockFactory(): LockFactory
    {
        return $this->lockFactory;
    }

    protected function getHttpClient(): SmartHttpClient
    {
        return $this->httpClient;
    }

    protected function getLogger(): LoggerInterface
    {
        return $this->logger;
    }

    protected function getCache(): CacheInterface
    {
        return $this->cache;
    }

    protected function getEventDispatcher(): EventDispatcherInterface
    {
        return $this->eventDispatcher;
    }

    protected function getAsyncInsertService(): AsyncInsertService
    {
        return $this->asyncInsertService;
    }

    public function getBaseUrl(): string
    {
        return 'https://open.feishu.cn';
    }

    public function generateAuthorizationUrl(?string $sessionId = null, ?string $codeChallenge = null, ?string $codeChallengeMethod = null): string
    {
        $config = $this->configRepository->findValidConfig();
        if (null === $config) {
            throw new LarkOAuth2ConfigurationException('No valid Lark OAuth2 configuration found');
        }

        $state = bin2hex(random_bytes(16));
        $stateEntity = new LarkOAuth2State();
        $stateEntity->setState($state);
        $stateEntity->setConfig($config);

        if (null !== $sessionId) {
            $stateEntity->setSessionId($sessionId);
        }

        $this->entityManager->persist($stateEntity);
        $this->entityManager->flush();

        $redirectUri = $this->urlGenerator->generate('lark_oauth2_callback', [], UrlGeneratorInterface::ABSOLUTE_URL);

        $params = [
            'client_id' => $config->getAppId(),
            'redirect_uri' => $redirectUri,
            'state' => $state,
        ];

        // 添加scope参数
        $scope = $config->getScope();
        if (null !== $scope && '' !== $scope) {
            $params['scope'] = $scope;
        }

        // PKCE支持
        if (null !== $codeChallenge) {
            $params['code_challenge'] = $codeChallenge;
            if (null !== $codeChallengeMethod) {
                $params['code_challenge_method'] = $codeChallengeMethod;
            }
        }

        return self::AUTHORIZE_URL . '?' . http_build_query($params);
    }

    public function handleCallback(string $code, string $state): LarkOAuth2User
    {
        $stateEntity = $this->stateRepository->findValidState($state);
        if (null === $stateEntity || !$stateEntity->isValid()) {
            throw new LarkOAuth2RuntimeException('Invalid or expired state');
        }

        $stateEntity->markAsUsed();
        $this->entityManager->persist($stateEntity);
        $this->entityManager->flush();

        $config = $stateEntity->getConfig();
        $redirectUri = $this->urlGenerator->generate('lark_oauth2_callback', [], UrlGeneratorInterface::ABSOLUTE_URL);

        // 获取access_token
        $tokenData = $this->getAccessToken($code, $config, $redirectUri);

        // 获取用户信息
        $accessToken = $tokenData['access_token'] ?? null;
        if (!is_string($accessToken)) {
            throw new LarkOAuth2ApiException('Invalid access token');
        }

        $userInfo = $this->getUserInfo($accessToken, $config);

        // 合并数据
        $userInfoData = $userInfo['data'] ?? $userInfo;
        if (!is_array($userInfoData)) {
            throw new LarkOAuth2ApiException('Invalid user info data');
        }

        /** @var array<string, mixed> $userData */
        $userData = array_merge($tokenData, $userInfoData);

        // 创建或更新用户
        $user = $this->userRepository->updateOrCreate($userData, $config);
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user;
    }

    /**
     * @return array<string, mixed>
     */
    private function getAccessToken(string $code, LarkOAuth2Config $config, string $redirectUri, ?string $codeVerifier = null): array
    {
        $request = new GetAccessTokenRequest();
        $request->setConfig($config);
        $request->setCode($code);
        $request->setRedirectUri($redirectUri);

        if (null !== $codeVerifier) {
            $request->setCodeVerifier($codeVerifier);
        }

        $data = $this->request($request);

        if (!is_array($data)) {
            throw new LarkOAuth2ApiException('Invalid response format');
        }

        if (!isset($data['access_token'])) {
            throw new LarkOAuth2ApiException('No access token received');
        }

        /** @var array<string, mixed> $data */
        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function getUserInfo(string $accessToken, LarkOAuth2Config $config): array
    {
        $request = new GetUserInfoRequest();
        $request->setConfig($config);
        $request->setAccessToken($accessToken);

        $data = $this->request($request);

        if (!is_array($data)) {
            throw new LarkOAuth2ApiException('Invalid response format');
        }

        /** @var array<string, mixed> $data */
        return $data;
    }

    public function refreshExpiredTokens(): int
    {
        $expiredUsers = $this->userRepository->findExpiredTokenUsers();
        $refreshed = 0;

        foreach ($expiredUsers as $user) {
            if ($this->refreshToken($user->getOpenId())) {
                ++$refreshed;
            }

            // 避免频率限制
            usleep(100000); // 0.1秒
        }

        return $refreshed;
    }

    public function refreshToken(string $openId): bool
    {
        $user = $this->userRepository->findByOpenId($openId);
        if (null === $user) {
            return false;
        }

        if (!$this->canRefreshToken($user)) {
            return false;
        }

        try {
            $data = $this->requestTokenRefresh($user);
            $this->updateUserTokens($user, $data);

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Failed to refresh Lark OAuth2 token', [
                'open_id' => $openId,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function canRefreshToken(LarkOAuth2User $user): bool
    {
        return null !== $user->getRefreshToken() && !$user->isRefreshTokenExpired();
    }

    /**
     * @return array<string, mixed>
     */
    private function requestTokenRefresh(LarkOAuth2User $user): array
    {
        $request = new RefreshTokenRequest();
        $request->setConfig($user->getConfig());
        $request->setRefreshToken((string) $user->getRefreshToken());

        $data = $this->request($request);

        if (!is_array($data)) {
            throw new LarkOAuth2ApiException('Invalid response format');
        }

        /** @var array<string, mixed> $data */
        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateUserTokens(LarkOAuth2User $user, array $data): void
    {
        $this->updateAccessToken($user, $data);
        $this->updateExpiresIn($user, $data);
        $this->updateRefreshToken($user, $data);
        $this->updateRefreshTokenExpiresIn($user, $data);
        $this->updateScope($user, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateAccessToken(LarkOAuth2User $user, array $data): void
    {
        if (isset($data['access_token']) && is_string($data['access_token'])) {
            $user->setAccessToken($data['access_token']);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateExpiresIn(LarkOAuth2User $user, array $data): void
    {
        if (isset($data['expires_in']) && is_int($data['expires_in'])) {
            $user->setExpiresIn($data['expires_in']);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateRefreshToken(LarkOAuth2User $user, array $data): void
    {
        if (isset($data['refresh_token']) && is_string($data['refresh_token'])) {
            $user->setRefreshToken($data['refresh_token']);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateRefreshTokenExpiresIn(LarkOAuth2User $user, array $data): void
    {
        if (isset($data['refresh_token_expires_in']) && is_int($data['refresh_token_expires_in'])) {
            $user->setRefreshTokenExpiresIn($data['refresh_token_expires_in']);
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    private function updateScope(LarkOAuth2User $user, array $data): void
    {
        if (isset($data['scope']) && is_string($data['scope'])) {
            $user->setScope($data['scope']);
        }
    }

    public function cleanupExpiredStates(): int
    {
        return $this->stateRepository->cleanupExpiredStates();
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchUserInfo(string $openId, bool $forceRefresh = false): array
    {
        $user = $this->userRepository->findByOpenId($openId);
        if (null === $user) {
            throw new LarkOAuth2RuntimeException('User not found');
        }

        if (!$forceRefresh && !$user->isTokenExpired() && null !== $user->getRawData()) {
            $rawData = $user->getRawData();
            // getRawData() returns ?array, so after null check it's guaranteed to be array
            /** @var array<string, mixed> $rawData */
            return $rawData;
        }

        if ($user->isTokenExpired() && null !== $user->getRefreshToken()) {
            $this->refreshToken($openId);
            $user = $this->userRepository->findByOpenId($openId);
        }

        if (null === $user) {
            throw new LarkOAuth2RuntimeException('User not found after token refresh');
        }

        $userInfo = $this->getUserInfo($user->getAccessToken(), $user->getConfig());
        $userData = $userInfo['data'] ?? $userInfo;

        if (!is_array($userData)) {
            throw new LarkOAuth2RuntimeException('Invalid user data format');
        }

        /** @var array<string, mixed> $userData */
        $userData = $userData;

        // 更新用户信息
        $user = $this->userRepository->updateOrCreate($userData, $user->getConfig());
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $userData;
    }

    protected function getRequestUrl(RequestInterface $request): string
    {
        return $request->getRequestPath();
    }

    protected function getRequestMethod(RequestInterface $request): string
    {
        return $request->getRequestMethod() ?? 'GET';
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function getRequestOptions(RequestInterface $request): ?array
    {
        return $request->getRequestOptions();
    }

    protected function formatResponse(RequestInterface $request, ResponseInterface $response): mixed
    {
        $data = $this->parseResponseContent($response);
        $this->validateResponseData($data);
        $this->checkLarkApiError($data);
        $this->checkOAuth2Error($data);

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    private function parseResponseContent(ResponseInterface $response): array
    {
        $content = $response->getContent();
        /** @var mixed $decoded */
        $decoded = json_decode($content, true);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new LarkOAuth2ApiException('Failed to parse response: ' . json_last_error_msg());
        }

        if (!is_array($decoded)) {
            throw new LarkOAuth2ApiException('Response is not an array');
        }

        /** @var array<string, mixed> $data */
        $data = $decoded;

        return $data;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function validateResponseData(array $data): void
    {
        // 此方法预留用于未来可能的响应数据验证逻辑
    }

    /**
     * @param array<string, mixed> $data
     */
    private function checkLarkApiError(array $data): void
    {
        if (!isset($data['code']) || 0 === $data['code']) {
            return;
        }

        $msg = is_string($data['msg'] ?? null) ? $data['msg'] : 'Unknown error';
        $errorDescription = is_string($data['error_description'] ?? null) ? $data['error_description'] : '';
        $code = is_int($data['code']) ? $data['code'] : 0;
        throw new LarkOAuth2ApiException(sprintf('Lark API error: %s %s', $msg, $errorDescription), $code);
    }

    /**
     * @param array<string, mixed> $data
     */
    private function checkOAuth2Error(array $data): void
    {
        if (!isset($data['error'])) {
            return;
        }

        $error = is_string($data['error']) ? $data['error'] : 'unknown';
        $errorDescription = isset($data['error_description']) && is_string($data['error_description'])
            ? $data['error_description']
            : 'Unknown error';
        throw new LarkOAuth2ApiException(sprintf('OAuth2 error: %s - %s', $error, $errorDescription));
    }
}
