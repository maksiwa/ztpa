<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Challenge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Challenge>
 */
class ChallengeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Challenge::class);
    }

    public function save(Challenge $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Challenge $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Znajdź wyzwania posortowane po trudności
     */
    public function findAllSortedByDifficulty(): array
    {
        return $this->createQueryBuilder('c')
            ->orderBy('c.difficultyLevel', 'ASC')
            ->addOrderBy('c.durationDays', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajdź wyzwania po poziomie trudności
     */
    public function findByDifficulty(string $level): array
    {
        return $this->findBy(
            ['difficultyLevel' => $level],
            ['points' => 'DESC']
        );
    }

    /**
     * Wyzwania z największą liczbą uczestników
     */
    public function findMostPopular(int $limit = 5): array
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.userChallenges', 'uc')
            ->groupBy('c.id')
            ->orderBy('COUNT(uc.id)', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
