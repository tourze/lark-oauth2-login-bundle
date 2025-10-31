<?php

namespace Tourze\LarkOAuth2LoginBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2State;
use Tourze\LarkOAuth2LoginBundle\Repository\LarkOAuth2StateRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(LarkOAuth2StateRepository::class)]
#[RunTestsInSeparateProcesses]
final class LarkOAuth2StateRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @return ServiceEntityRepository<LarkOAuth2State>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(LarkOAuth2StateRepository::class);
    }

    /**
     * @return LarkOAuth2StateRepository
     */
    private function getStateRepository(): LarkOAuth2StateRepository
    {
        return self::getService(LarkOAuth2StateRepository::class);
    }

    protected function onSetUp(): void
    {
        // 这个测试类不需要额外的设置
    }

    public function testFindOneByWithOrderBy(): void
    {
        self::getEntityManager()->createQuery('DELETE FROM Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2State')->execute();

        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $state1 = $this->createTestEntity($config, 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa1');
        $state1->setSessionId('order-session-1');

        $state2 = $this->createTestEntity($config, 'aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa2');
        $state2->setSessionId('order-session-2');

        self::getEntityManager()->persist($state1);
        self::getEntityManager()->persist($state2);
        self::getEntityManager()->flush();

        $result = $this->getRepository()->findOneBy([], ['state' => 'ASC']);
        $this->assertInstanceOf(LarkOAuth2State::class, $result);
        $this->assertEquals('aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa1', $result->getState());
    }

    public function testCountWithNullConditionShouldReturnCorrectNumber(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $state = $this->createTestEntity($config);
        self::getEntityManager()->persist($state);
        self::getEntityManager()->flush();

        $count = $this->getRepository()->count(['sessionId' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThan(0, $count);
    }

    public function testCountWithNullConditionForUsedTime(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $state = $this->createTestEntity($config);
        self::getEntityManager()->persist($state);
        self::getEntityManager()->flush();

        $count = $this->getRepository()->count(['usedTime' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThan(0, $count);
    }

    public function testCountByAssociationConfigShouldReturnCorrectNumber(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $state = $this->createTestEntity($config);
        self::getEntityManager()->persist($state);
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

        $state = $this->createTestEntity($config);
        self::getEntityManager()->persist($state);
        self::getEntityManager()->flush();

        $result = $this->getRepository()->findOneBy(['config' => $config]);
        $this->assertInstanceOf(LarkOAuth2State::class, $result);
        $this->assertEquals($config->getId(), $result->getConfig()->getId());
    }

    public function testSaveWithFlush(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $state = $this->createTestEntity($config);
        $uniqueSessionId = 'save-session-' . uniqid();
        $state->setSessionId($uniqueSessionId);

        $this->getStateRepository()->save($state, true);

        $found = $this->getRepository()->findOneBy(['state' => $state->getState()]);
        $this->assertNotNull($found);
        $this->assertEquals($uniqueSessionId, $found->getSessionId());
    }

    public function testSaveWithoutFlush(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $state = $this->createTestEntity($config, 'noflush12345678901234567890123456');
        $uniqueSessionId = 'no-flush-session-' . uniqid();
        $state->setSessionId($uniqueSessionId);

        $this->getStateRepository()->save($state, false);
        self::getEntityManager()->flush();

        $found = $this->getRepository()->findOneBy(['state' => 'noflush12345678901234567890123456']);
        $this->assertNotNull($found);
        $this->assertEquals($uniqueSessionId, $found->getSessionId());
    }

    public function testFindValidStateExists(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $state = $this->createTestEntity($config, 'validstate123456789012345678901234');
        $uniqueSessionId = 'valid-state-session-' . uniqid();
        $state->setSessionId($uniqueSessionId);
        self::getEntityManager()->persist($state);
        self::getEntityManager()->flush();

        $result = $this->getStateRepository()->findValidState('validstate123456789012345678901234');
        $this->assertInstanceOf(LarkOAuth2State::class, $result);
        $this->assertTrue($result->isValid());
    }

    public function testFindValidStateNotFound(): void
    {
        $result = $this->getStateRepository()->findValidState('non-existent-state-' . uniqid());
        $this->assertNull($result);
    }

    public function testFindValidStateExpiredShouldReturnNull(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $state = $this->createTestEntity($config, 'expiredst123456789012345678901234');
        $uniqueSessionId = 'expired-session-' . uniqid();
        $state->setSessionId($uniqueSessionId);
        self::getEntityManager()->persist($state);
        self::getEntityManager()->flush();

        sleep(1);

        $expiredTime = new \DateTimeImmutable('-1 hour');
        self::getEntityManager()->createQuery(
            'UPDATE Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2State s SET s.expiresTime = :expiredTime WHERE s.state = :state'
        )
            ->setParameter('expiredTime', $expiredTime)
            ->setParameter('state', 'expiredst123456789012345678901234')
            ->execute()
        ;

        self::getEntityManager()->clear();

        $result = $this->getStateRepository()->findValidState('expiredst123456789012345678901234');
        $this->assertNull($result);
    }

    public function testFindValidStateUsedShouldReturnNull(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $state = $this->createTestEntity($config, 'usedstate123456789012345678901234');
        $uniqueSessionId = 'used-session-' . uniqid();
        $state->setSessionId($uniqueSessionId);
        $state->markAsUsed();
        self::getEntityManager()->persist($state);
        self::getEntityManager()->flush();

        $result = $this->getStateRepository()->findValidState('usedstate123456789012345678901234');
        $this->assertNull($result);
    }

    public function testCleanupExpiredStatesReturnsInteger(): void
    {
        $count = $this->getStateRepository()->cleanupExpiredStates();
        $this->assertIsInt($count);
        $this->assertGreaterThanOrEqual(0, $count);
    }

    public function testCleanupExpiredStatesRemovesExpiredStates(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $expiredState = $this->createTestEntity($config, 'cleanupex123456789012345678901234');
        $expiredState->setSessionId('cleanup-expired-' . uniqid());
        self::getEntityManager()->persist($expiredState);

        $validState = $this->createTestEntity($config, 'cleanupva123456789012345678901234');
        $validState->setSessionId('cleanup-valid-' . uniqid());
        self::getEntityManager()->persist($validState);
        self::getEntityManager()->flush();

        $expiredTime = new \DateTimeImmutable('-1 hour');
        self::getEntityManager()->createQuery(
            'UPDATE Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2State s SET s.expiresTime = :expiredTime WHERE s.state = :state'
        )
            ->setParameter('expiredTime', $expiredTime)
            ->setParameter('state', 'cleanupex123456789012345678901234')
            ->execute()
        ;

        self::getEntityManager()->clear();

        $deletedCount = $this->getStateRepository()->cleanupExpiredStates();
        $this->assertGreaterThan(0, $deletedCount);

        $expiredFound = $this->getRepository()->findOneBy(['state' => 'cleanupex123456789012345678901234']);
        $this->assertNull($expiredFound);

        $validFound = $this->getRepository()->findOneBy(['state' => 'cleanupva123456789012345678901234']);
        $this->assertNotNull($validFound);
    }

    public function testFindBySessionIdReturnsArray(): void
    {
        $result = $this->getStateRepository()->findBySessionId('non-existent-session-' . uniqid());
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testFindBySessionIdWithExistingSession(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $sessionId = 'test-session-find-by-' . uniqid();

        $state1 = $this->createTestEntity($config, 'sesstest123456789012345678901234');
        $state1->setSessionId($sessionId);

        $state2 = $this->createTestEntity($config, 'sesstes2123456789012345678901234');
        $state2->setSessionId($sessionId);

        self::getEntityManager()->persist($state1);
        self::getEntityManager()->persist($state2);
        self::getEntityManager()->flush();

        $results = $this->getStateRepository()->findBySessionId($sessionId);
        $this->assertIsArray($results);
        $this->assertCount(2, $results);

        foreach ($results as $result) {
            $this->assertInstanceOf(LarkOAuth2State::class, $result);
            $this->assertEquals($sessionId, $result->getSessionId());
        }
    }

    public function testFindBySessionIdOrdersByCreateTime(): void
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $sessionId = 'test-session-order-' . uniqid();

        $state1 = $this->createTestEntity($config, 'orderte1123456789012345678901234');
        $state1->setSessionId($sessionId);
        self::getEntityManager()->persist($state1);
        self::getEntityManager()->flush();

        usleep(1000);

        $state2 = $this->createTestEntity($config, 'orderte2123456789012345678901234');
        $state2->setSessionId($sessionId);
        self::getEntityManager()->persist($state2);
        self::getEntityManager()->flush();

        $results = $this->getStateRepository()->findBySessionId($sessionId);
        $this->assertIsArray($results);
        $this->assertCount(2, $results);

        $this->assertEquals('orderte2123456789012345678901234', $results[0]->getState());
        $this->assertEquals('orderte1123456789012345678901234', $results[1]->getState());
    }

    private function createConfig(): LarkOAuth2Config
    {
        $config = new LarkOAuth2Config();
        $config->setAppId('test-app-id-' . uniqid());
        $config->setAppSecret('test-secret');
        $config->setValid(true);

        return $config;
    }

    private function createTestEntity(LarkOAuth2Config $config, ?string $stateValue = null): LarkOAuth2State
    {
        if (null === $stateValue) {
            $stateValue = str_pad(dechex(mt_rand(0, 0xFFFFFFFF)), 32, '0', STR_PAD_LEFT);
        }

        $entity = new LarkOAuth2State();
        $entity->setState($stateValue);
        $entity->setConfig($config);

        return $entity;
    }

    protected function createNewEntity(): object
    {
        $config = $this->createConfig();
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $state = new LarkOAuth2State();
        $state->setState(str_pad(dechex(mt_rand(0, 0xFFFFFFFF)), 32, '0', STR_PAD_LEFT));
        $state->setConfig($config);
        $state->setSessionId('test-session-' . uniqid());

        return $state;
    }
}
