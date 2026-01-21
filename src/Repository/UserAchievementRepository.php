<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserAchievement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserAchievement>
 */
class UserAchievementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserAchievement::class);
    }

    public function save(UserAchievement $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Pobierz wszystkie osiągnięcia użytkownika
     */
    public function findByUser(User $user): array
    {
        return $this->findBy(['user' => $user], ['earnedAt' => 'DESC']);
    }

    /**
     * Sprawdź czy użytkownik ma już dane osiągnięcie
     */
    public function hasAchievement(User $user, int $achievementId): bool
    {
        return $this->count(['user' => $user, 'achievement' => $achievementId]) > 0;
    }
}
