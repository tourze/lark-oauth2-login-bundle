<?php

namespace Tourze\LarkOAuth2LoginBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2User;
use Tourze\LarkOAuth2LoginBundle\Exception\InvalidArgumentException;
use Tourze\LarkOAuth2LoginBundle\Repository\LarkOAuth2UserRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(LarkOAuth2UserRepository::class)]
#[RunTestsInSeparateProcesses]
final class LarkOAuth2UserRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @return ServiceEntityRepository<LarkOAuth2User>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(LarkOAuth2UserRepository::class);
    }

    /**
     * @return LarkOAuth2UserRepository
     */
    private function getUserRepository(): LarkOAuth2UserRepository
    {
        return self::getService(LarkOAuth2UserRepository::class);
    }

    protected function onSetUp(): void
    {
        // 这个测试类不需要额外的设置
    }

    public function testFindOneByWithOrderBy(): void
    {
        self::getEntityManager()->createQuery('DELETE FROM Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2User')->execute();

        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $user1 = $this->createTestEntity($config, 'order-user-a-' . uniqid());
        $user1->setName('User A');

        $user2 = $this->createTestEntity($config, 'order-user-b-' . uniqid());
        $user2->setName('User B');

        self::getEntityManager()->persist($user1);
        self::getEntityManager()->persist($user2);
        self::getEntityManager()->flush();

        $result = $this->getRepository()->findOneBy([], ['name' => 'ASC']);
        $this->assertInstanceOf(LarkOAuth2User::class, $result);
        $this->assertEquals('User A', $result->getName());
    }

    public function testCountWithNullConditionShouldReturnCorrectNumber(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $user = $this->createTestEntity($config, 'null-count-user-' . uniqid());
        $user->setUserId(null);
        self::getEntityManager()->persist($user);
        self::getEntityManager()->flush();

        $count = $this->getRepository()->count(['userId' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThan(0, $count);
    }

    public function testCountWithNullConditionForRefreshTokenExpiresTime(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $user = $this->createTestEntity($config, 'null-refresh-user-' . uniqid());
        self::getEntityManager()->persist($user);
        self::getEntityManager()->flush();

        $count = $this->getRepository()->count(['refreshTokenExpiresTime' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThan(0, $count);
    }

    public function testCountByAssociationConfigShouldReturnCorrectNumber(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $user = $this->createTestEntity($config, 'config-relation-user-' . uniqid());
        self::getEntityManager()->persist($user);
        self::getEntityManager()->flush();

        $count = $this->getRepository()->count(['config' => $config]);
        $this->assertIsInt($count);
        $this->assertGreaterThan(0, $count);
    }

    public function testFindOneByAssociationConfigShouldReturnMatchingEntity(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $user = $this->createTestEntity($config, 'findone-by-config-user-' . uniqid());
        self::getEntityManager()->persist($user);
        self::getEntityManager()->flush();

        $result = $this->getRepository()->findOneBy(['config' => $config]);
        $this->assertInstanceOf(LarkOAuth2User::class, $result);
        $this->assertEquals($config->getId(), $result->getConfig()->getId());
    }

    public function testSaveWithFlush(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $uniqueOpenId = 'save-test-open-id-' . uniqid();
        $user = $this->createTestEntity($config, $uniqueOpenId);
        $user->setName('Save Test User');

        $this->getUserRepository()->save($user, true);

        $found = $this->getUserRepository()->findByOpenId($uniqueOpenId);
        $this->assertNotNull($found);
        $this->assertEquals($uniqueOpenId, $found->getOpenId());
        $this->assertEquals('Save Test User', $found->getName());
    }

    public function testSaveWithoutFlush(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $uniqueOpenId = 'save-no-flush-open-id-' . uniqid();
        $user = $this->createTestEntity($config, $uniqueOpenId);
        $user->setName('Save No Flush User');

        $this->getUserRepository()->save($user, false);
        self::getEntityManager()->flush();

        $found = $this->getUserRepository()->findByOpenId($uniqueOpenId);
        $this->assertNotNull($found);
        $this->assertEquals($uniqueOpenId, $found->getOpenId());
        $this->assertEquals('Save No Flush User', $found->getName());
    }

    public function testFindByOpenIdExists(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $uniqueOpenId = 'find-by-open-id-test-' . uniqid();
        $user = $this->createTestEntity($config, $uniqueOpenId);
        $user->setName('Find By OpenId Test');
        self::getEntityManager()->persist($user);
        self::getEntityManager()->flush();

        $result = $this->getUserRepository()->findByOpenId($uniqueOpenId);
        $this->assertInstanceOf(LarkOAuth2User::class, $result);
        $this->assertEquals($uniqueOpenId, $result->getOpenId());
        $this->assertEquals('Find By OpenId Test', $result->getName());
    }

    public function testFindByOpenIdNotFound(): void
    {
        $result = $this->getUserRepository()->findByOpenId('non-existent-open-id-' . uniqid());
        $this->assertNull($result);
    }

    public function testFindByUnionIdExists(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $uniqueUnionId = 'find-by-union-id-test-' . uniqid();
        $user = $this->createTestEntity($config, 'find-by-union-id-open-id-' . uniqid());
        $user->setUnionId($uniqueUnionId);
        $user->setName('Find By UnionId Test');
        self::getEntityManager()->persist($user);
        self::getEntityManager()->flush();

        $result = $this->getUserRepository()->findByUnionId($uniqueUnionId);
        $this->assertInstanceOf(LarkOAuth2User::class, $result);
        $this->assertEquals($uniqueUnionId, $result->getUnionId());
        $this->assertEquals('Find By UnionId Test', $result->getName());
    }

    public function testFindByUnionIdNotFound(): void
    {
        $result = $this->getUserRepository()->findByUnionId('non-existent-union-id-' . uniqid());
        $this->assertNull($result);
    }

    public function testFindByUserIdExists(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $uniqueUserId = 'find-by-user-id-test-' . uniqid();
        $user = $this->createTestEntity($config, 'find-by-user-id-open-id-' . uniqid());
        $user->setUserId($uniqueUserId);
        $user->setName('Find By UserId Test');
        self::getEntityManager()->persist($user);
        self::getEntityManager()->flush();

        $result = $this->getUserRepository()->findByUserId($uniqueUserId);
        $this->assertInstanceOf(LarkOAuth2User::class, $result);
        $this->assertEquals($uniqueUserId, $result->getUserId());
        $this->assertEquals('Find By UserId Test', $result->getName());
    }

    public function testFindByUserIdNotFound(): void
    {
        $result = $this->getUserRepository()->findByUserId('non-existent-user-id-' . uniqid());
        $this->assertNull($result);
    }

    public function testFindExpiredTokenUsersReturnsArray(): void
    {
        $result = $this->getUserRepository()->findExpiredTokenUsers();
        $this->assertIsArray($result);
    }

    public function testFindExpiredTokenUsersWithExpiredTokens(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $expiredUser = $this->createTestEntity($config, 'expired-token-user-' . uniqid());
        $expiredUser->setRefreshToken('refresh-token');
        $expiredUser->setRefreshTokenExpiresIn(7200);
        self::getEntityManager()->persist($expiredUser);

        $validUser = $this->createTestEntity($config, 'valid-token-user-' . uniqid());
        $validUser->setRefreshToken('valid-refresh-token');
        $validUser->setRefreshTokenExpiresIn(7200);
        self::getEntityManager()->persist($validUser);

        self::getEntityManager()->flush();

        $expiredTime = new \DateTimeImmutable('-1 hour');
        self::getEntityManager()->createQuery(
            'UPDATE Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2User u SET u.tokenExpiresTime = :expiredTime WHERE u.openId = :openId'
        )
            ->setParameter('expiredTime', $expiredTime)
            ->setParameter('openId', $expiredUser->getOpenId())
            ->execute()
        ;

        self::getEntityManager()->clear();

        $results = $this->getUserRepository()->findExpiredTokenUsers();
        $this->assertIsArray($results);
        $this->assertGreaterThan(0, count($results));

        $foundExpiredUser = false;
        foreach ($results as $result) {
            $this->assertInstanceOf(LarkOAuth2User::class, $result);
            $this->assertTrue($result->isTokenExpired());
            $this->assertNotNull($result->getRefreshToken());

            if ($expiredUser->getOpenId() === $result->getOpenId()) {
                $foundExpiredUser = true;
            }
        }

        $this->assertTrue($foundExpiredUser);
    }

    public function testFindExpiredTokenUsersWithLimit(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        for ($i = 1; $i <= 10; ++$i) {
            $user = $this->createTestEntity($config, 'expired-limit-user-' . $i . '-' . uniqid());
            $user->setRefreshToken('refresh-token-' . $i);
            $user->setRefreshTokenExpiresIn(7200);
            self::getEntityManager()->persist($user);
        }
        self::getEntityManager()->flush();

        $expiredTime = new \DateTimeImmutable('-1 hour');
        self::getEntityManager()->createQuery(
            'UPDATE Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2User u SET u.tokenExpiresTime = :expiredTime WHERE u.refreshToken IS NOT NULL'
        )
            ->setParameter('expiredTime', $expiredTime)
            ->execute()
        ;

        self::getEntityManager()->clear();

        $results = $this->getUserRepository()->findExpiredTokenUsers(3);
        $this->assertIsArray($results);
        $this->assertLessThanOrEqual(3, count($results));
    }

    public function testUpdateOrCreateWithoutOpenId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('open_id is required');

        $config = $this->createConfig();
        $this->getUserRepository()->updateOrCreate([], $config);
    }

    public function testUpdateOrCreateWithEmptyOpenId(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('open_id is required');

        $config = $this->createConfig();
        $this->getUserRepository()->updateOrCreate(['open_id' => ''], $config);
    }

    public function testUpdateOrCreateCreatesNewUser(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $uniqueOpenId = 'new-user-open-id-' . uniqid();
        $userData = [
            'open_id' => $uniqueOpenId,
            'name' => 'New User',
            'email' => 'newuser@example.com',
        ];

        $result = $this->getUserRepository()->updateOrCreate($userData, $config);

        $this->assertInstanceOf(LarkOAuth2User::class, $result);
        $this->assertEquals($uniqueOpenId, $result->getOpenId());
        $this->assertEquals('New User', $result->getName());
        $this->assertEquals('newuser@example.com', $result->getEmail());
    }

    public function testUpdateOrCreateUpdatesExistingUser(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $uniqueOpenId = 'existing-user-open-id-' . uniqid();
        $existingUser = $this->createTestEntity($config, $uniqueOpenId);
        $existingUser->setName('Old Name');
        $existingUser->setEmail('old@example.com');
        self::getEntityManager()->persist($existingUser);
        self::getEntityManager()->flush();

        $userData = [
            'open_id' => $uniqueOpenId,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];

        $result = $this->getUserRepository()->updateOrCreate($userData, $config);

        $this->assertInstanceOf(LarkOAuth2User::class, $result);
        $this->assertEquals($uniqueOpenId, $result->getOpenId());
        $this->assertEquals('Updated Name', $result->getName());
        $this->assertEquals('updated@example.com', $result->getEmail());

        $this->assertEquals($existingUser->getId(), $result->getId());
    }

    public function testRelationshipWithConfig(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $user = $this->createTestEntity($config, 'relationship-test-open-id-' . uniqid());
        self::getEntityManager()->persist($user);
        self::getEntityManager()->flush();

        $this->assertEquals($config->getId(), $user->getConfig()->getId());
        $this->assertEquals($config->getAppId(), $user->getConfig()->getAppId());
    }

    private function createConfig(): LarkOAuth2Config
    {
        $config = new LarkOAuth2Config();
        $config->setAppId('test-app-id-' . uniqid());
        $config->setAppSecret('test-secret');
        $config->setValid(true);

        return $config;
    }

    private function createTestEntity(LarkOAuth2Config $config, string $openId): LarkOAuth2User
    {
        $user = new LarkOAuth2User();
        $user->setConfig($config);
        $user->setOpenId($openId);
        $user->setAccessToken('test-access-token');
        $user->setExpiresIn(3600);

        return $user;
    }

    protected function createNewEntity(): object
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        return $this->createTestEntity($config, 'test-open-id-' . uniqid());
    }
}
