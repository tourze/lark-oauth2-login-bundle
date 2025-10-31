<?php

namespace Tourze\LarkOAuth2LoginBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2State;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<LarkOAuth2State>
 */
#[AsRepository(entityClass: LarkOAuth2State::class)]
class LarkOAuth2StateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LarkOAuth2State::class);
    }

    public function findValidState(string $state): ?LarkOAuth2State
    {
        $stateEntity = $this->findOneBy(['state' => $state]);

        if (null === $stateEntity || !$stateEntity->isValid()) {
            return null;
        }

        return $stateEntity;
    }

    public function cleanupExpiredStates(): int
    {
        $qb = $this->createQueryBuilder('s');

        $result = $qb->delete()
            ->where('s.expiresTime < :now')
            ->setParameter('now', new \DateTimeImmutable())
            ->getQuery()
            ->execute();

        return is_int($result) ? $result : 0;
    }

    /**
     * @return array<LarkOAuth2State>
     */
    public function findBySessionId(string $sessionId): array
    {
        $result = $this->createQueryBuilder('s')
            ->where('s.sessionId = :sessionId')
            ->setParameter('sessionId', $sessionId)
            ->orderBy('s.createTime', 'DESC')
            ->getQuery()
            ->getResult();

        /** @var array<LarkOAuth2State> $result */
        return $result;
    }

    public function save(LarkOAuth2State $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LarkOAuth2State $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
