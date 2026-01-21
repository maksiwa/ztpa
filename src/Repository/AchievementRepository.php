<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Achievement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Achievement>
 */
class AchievementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Achievement::class);
    }

    public function save(Achievement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Znajdź osiągnięcia dostępne dla użytkownika z daną liczbą punktów
     */
    public function findAvailableForPoints(int $points): array
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.pointsRequired <= :points')
            ->setParameter('points', $points)
            ->orderBy('a.pointsRequired', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajdź wszystkie posortowane po wymaganych punktach
     */
    public function findAllSorted(): array
    {
        return $this->findBy([], ['pointsRequired' => 'ASC']);
    }
}
