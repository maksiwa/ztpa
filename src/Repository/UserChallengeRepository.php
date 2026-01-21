<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use App\Entity\UserChallenge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<UserChallenge>
 */
class UserChallengeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, UserChallenge::class);
    }

    public function save(UserChallenge $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(UserChallenge $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Pobierz aktywne wyzwania użytkownika (status = in_progress)
     */
    public function findActiveForUser(User $user): array
    {
        return $this->createQueryBuilder('uc')
            ->andWhere('uc.user = :user')
            ->andWhere('uc.status = :status')
            ->setParameter('user', $user)
            ->setParameter('status', 'in_progress')
            ->orderBy('uc.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Pobierz wszystkie wyzwania użytkownika (historia)
     */
    public function findAllForUser(User $user): array
    {
        return $this->createQueryBuilder('uc')
            ->andWhere('uc.user = :user')
            ->setParameter('user', $user)
            ->orderBy('uc.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Sprawdź czy użytkownik już uczestniczy w wyzwaniu
     */
    public function isUserParticipating(User $user, int $challengeId): bool
    {
        $result = $this->createQueryBuilder('uc')
            ->select('COUNT(uc.id)')
            ->andWhere('uc.user = :user')
            ->andWhere('uc.challenge = :challengeId')
            ->andWhere('uc.status = :status')
            ->setParameter('user', $user)
            ->setParameter('challengeId', $challengeId)
            ->setParameter('status', 'in_progress')
            ->getQuery()
            ->getSingleScalarResult();

        return $result > 0;
    }

    /**
     * Pobierz ukończone wyzwania użytkownika
     */
    public function findCompletedForUser(User $user): array
    {
        return $this->findBy([
            'user' => $user,
            'status' => 'completed'
        ], ['createdAt' => 'DESC']);
    }
}
