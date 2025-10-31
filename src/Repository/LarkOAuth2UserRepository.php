<?php

namespace Tourze\LarkOAuth2LoginBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2User;
use Tourze\LarkOAuth2LoginBundle\Exception\InvalidArgumentException;
use Tourze\LarkOAuth2LoginBundle\Factory\LarkOAuth2UserFactory;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<LarkOAuth2User>
 */
#[AsRepository(entityClass: LarkOAuth2User::class)]
class LarkOAuth2UserRepository extends ServiceEntityRepository
{
    public function __construct(
        ManagerRegistry $registry,
        private readonly LarkOAuth2UserFactory $userFactory,
    ) {
        parent::__construct($registry, LarkOAuth2User::class);
    }

    public function findByUnionId(string $unionId): ?LarkOAuth2User
    {
        return $this->findOneBy(['unionId' => $unionId]);
    }

    public function findByUserId(string $userId): ?LarkOAuth2User
    {
        return $this->findOneBy(['userId' => $userId]);
    }

    /**
     * @param array<string, mixed> $userData
     */
    public function updateOrCreate(array $userData, LarkOAuth2Config $config): LarkOAuth2User
    {
        $openId = $userData['open_id'] ?? null;
        if (!is_string($openId) || '' === $openId) {
            throw new InvalidArgumentException('open_id is required and must be a non-empty string');
        }

        $user = $this->findOrCreateUser($openId, $config);
        $this->userFactory->updateFromUserData($user, $userData);

        return $user;
    }

    private function findOrCreateUser(string $openId, LarkOAuth2Config $config): LarkOAuth2User
    {
        $user = $this->findByOpenId($openId);
        if (null === $user) {
            $user = $this->userFactory->create($openId, $config);
        }

        return $user;
    }

    public function findByOpenId(string $openId): ?LarkOAuth2User
    {
        return $this->findOneBy(['openId' => $openId]);
    }

    /**
     * @return array<LarkOAuth2User>
     */
    public function findExpiredTokenUsers(int $limit = 100): array
    {
        $result = $this->createQueryBuilder('u')
            ->where('u.tokenExpiresTime < :now')
            ->andWhere('u.refreshToken IS NOT NULL')
            ->andWhere('u.refreshTokenExpiresTime > :now OR u.refreshTokenExpiresTime IS NULL')
            ->setParameter('now', new \DateTimeImmutable())
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        /** @var array<LarkOAuth2User> $result */
        return $result;
    }

    public function save(LarkOAuth2User $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LarkOAuth2User $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
