<?php

namespace Tourze\LarkOAuth2LoginBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;
use Tourze\LarkOAuth2LoginBundle\Repository\LarkOAuth2ConfigRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(LarkOAuth2ConfigRepository::class)]
#[RunTestsInSeparateProcesses]
final class LarkOAuth2ConfigRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @return ServiceEntityRepository<LarkOAuth2Config>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return self::getService(LarkOAuth2ConfigRepository::class);
    }

    /**
     * @return LarkOAuth2ConfigRepository
     */
    private function getConfigRepository(): LarkOAuth2ConfigRepository
    {
        return self::getService(LarkOAuth2ConfigRepository::class);
    }

    protected function onSetUp(): void
    {
        // 这个测试类不需要额外的设置
    }

    public function testFindOneByWithOrderBy(): void
    {
        self::getEntityManager()->createQuery('DELETE FROM Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config')->execute();

        $config1 = $this->createTestEntity();
        $config1->setAppId('order-app-a-' . uniqid());

        $config2 = $this->createTestEntity();
        $config2->setAppId('order-app-b-' . uniqid());

        self::getEntityManager()->persist($config1);
        self::getEntityManager()->persist($config2);
        self::getEntityManager()->flush();

        $result = $this->getRepository()->findOneBy(['isValid' => true], ['appId' => 'ASC']);
        $this->assertInstanceOf(LarkOAuth2Config::class, $result);
        $this->assertEquals($config1->getAppId(), $result->getAppId());
    }

    public function testCountWithNullConditionShouldReturnCorrectNumber(): void
    {
        $config = $this->createTestEntity();
        $config->setScope(null);
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $count = $this->getRepository()->count(['scope' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThan(0, $count);
    }

    public function testCountWithNullConditionForRemark(): void
    {
        $config = $this->createTestEntity();
        $config->setRemark(null);
        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $count = $this->getRepository()->count(['remark' => null]);
        $this->assertIsInt($count);
        $this->assertGreaterThan(0, $count);
    }

    public function testSaveWithFlush(): void
    {
        $config = $this->createTestEntity();
        $uniqueAppId = 'save-test-app-' . uniqid();
        $config->setAppId($uniqueAppId);

        $this->getConfigRepository()->save($config, true);

        $found = $this->getConfigRepository()->findByAppId($uniqueAppId);
        $this->assertNotNull($found);
        $this->assertEquals($uniqueAppId, $found->getAppId());
    }

    public function testSaveWithoutFlush(): void
    {
        $config = $this->createTestEntity();
        $uniqueAppId = 'save-no-flush-app-' . uniqid();
        $config->setAppId($uniqueAppId);

        $this->getConfigRepository()->save($config, false);
        self::getEntityManager()->flush();

        $found = $this->getConfigRepository()->findByAppId($uniqueAppId);
        $this->assertNotNull($found);
        $this->assertEquals($uniqueAppId, $found->getAppId());
    }

    public function testFindByAppIdExists(): void
    {
        $config = $this->createTestEntity();
        $uniqueAppId = 'test-app-id-exists-' . uniqid();
        $config->setAppId($uniqueAppId);

        self::getEntityManager()->persist($config);
        self::getEntityManager()->flush();

        $result = $this->getConfigRepository()->findByAppId($uniqueAppId);
        $this->assertNotNull($result);
        $this->assertEquals($uniqueAppId, $result->getAppId());
    }

    public function testFindByAppIdNotFound(): void
    {
        $result = $this->getConfigRepository()->findByAppId('non-existent-app-id-' . uniqid());
        $this->assertNull($result);
    }

    public function testFindValidConfigExists(): void
    {
        $invalidConfig = $this->createTestEntity();
        $invalidConfig->setAppId('invalid-app-' . uniqid());
        $invalidConfig->setValid(false);

        $validConfig = $this->createTestEntity();
        $validConfig->setAppId('valid-app-' . uniqid());
        $validConfig->setValid(true);

        self::getEntityManager()->persist($invalidConfig);
        self::getEntityManager()->persist($validConfig);
        self::getEntityManager()->flush();

        $result = $this->getConfigRepository()->findValidConfig();
        $this->assertNotNull($result);
        $this->assertTrue($result->isValid());
    }

    public function testFindValidConfigNotFound(): void
    {
        self::getEntityManager()->createQuery('DELETE FROM Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config')->execute();

        $invalidConfig = $this->createTestEntity();
        $invalidConfig->setAppId('only-invalid-app-' . uniqid());
        $invalidConfig->setValid(false);

        self::getEntityManager()->persist($invalidConfig);
        self::getEntityManager()->flush();

        $result = $this->getConfigRepository()->findValidConfig();
        $this->assertNull($result);
    }

    public function testFindValidConfigOrdersCorrectly(): void
    {
        self::getEntityManager()->createQuery('DELETE FROM Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config')->execute();

        $config1 = $this->createTestEntity();
        $config1->setAppId('valid-app-1-' . uniqid());

        $config2 = $this->createTestEntity();
        $config2->setAppId('valid-app-2-' . uniqid());

        self::getEntityManager()->persist($config1);
        self::getEntityManager()->persist($config2);
        self::getEntityManager()->flush();

        $result = $this->getConfigRepository()->findValidConfig();
        $this->assertNotNull($result);
        $this->assertEquals($config1->getAppId(), $result->getAppId());
    }

    private function createTestEntity(): LarkOAuth2Config
    {
        $config = new LarkOAuth2Config();
        $config->setAppId('test-app-id-' . uniqid());
        $config->setAppSecret('test-secret');
        $config->setValid(true);

        return $config;
    }

    protected function createNewEntity(): object
    {
        $config = new LarkOAuth2Config();
        $config->setAppId('test-app-' . uniqid());
        $config->setAppSecret('test-secret-' . uniqid());
        $config->setValid(true);
        $config->setScope('test-scope');
        $config->setRemark('test-remark');

        return $config;
    }
}
