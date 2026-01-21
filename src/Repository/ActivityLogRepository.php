<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ActivityLog;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ActivityLog>
 */
class ActivityLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ActivityLog::class);
    }

    public function save(ActivityLog $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Pobierz ostatnie logi użytkownika
     */
    public function findRecentByUser(User $user, int $limit = 20): array
    {
        return $this->createQueryBuilder('al')
            ->andWhere('al.user = :user')
            ->setParameter('user', $user)
            ->orderBy('al.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Pobierz logi dla admina (wszystkie)
     */
    public function findRecent(int $limit = 50): array
    {
        return $this->createQueryBuilder('al')
            ->orderBy('al.createdAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Pobierz logi po akcji
     */
    public function findByAction(string $action, int $limit = 50): array
    {
        return $this->findBy(
            ['action' => $action],
            ['createdAt' => 'DESC'],
            $limit
        );
    }
}
