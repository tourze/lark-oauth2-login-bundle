<?php

namespace Tourze\LarkOAuth2LoginBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Tourze\LarkOAuth2LoginBundle\Entity\LarkOAuth2Config;
use Tourze\PHPUnitSymfonyKernelTest\Attribute\AsRepository;

/**
 * @extends ServiceEntityRepository<LarkOAuth2Config>
 */
#[AsRepository(entityClass: LarkOAuth2Config::class)]
class LarkOAuth2ConfigRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LarkOAuth2Config::class);
    }

    public function findValidConfig(): ?LarkOAuth2Config
    {
        $result = $this->createQueryBuilder('c')
            ->where('c.isValid = :valid')
            ->setParameter('valid', true)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $result instanceof LarkOAuth2Config ? $result : null;
    }

    public function findByAppId(string $appId): ?LarkOAuth2Config
    {
        return $this->findOneBy(['appId' => $appId]);
    }

    public function save(LarkOAuth2Config $entity, bool $flush = true): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(LarkOAuth2Config $entity, bool $flush = true): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
