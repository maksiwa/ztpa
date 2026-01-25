<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * API dla rankingu i streak
 */
#[Route('/api/leaderboard')]
#[IsGranted('ROLE_USER')]
class LeaderboardController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private EntityManagerInterface $entityManager,
    ) {}

    /**
     * Pobierz ranking TOP 10 użytkowników
     */
    #[Route('', name: 'api_leaderboard', methods: ['GET'])]
    public function index(): JsonResponse
    {
        // Pobierz użytkowników posortowanych po punktach
        $users = $this->userRepository->findTopByPoints(10);
        
        /** @var User $currentUser */
        $currentUser = $this->getUser();
        $currentUserRank = $this->userRepository->getUserRank($currentUser);
        
        $leaderboard = [];
        $rank = 1;
        
        foreach ($users as $user) {
            $leaderboard[] = [
                'rank' => $rank,
                'id' => $user->getId(),
                'name' => $user->getFullName(),
                'points' => $user->getTotalPoints(),
                'streak' => $user->getCurrentStreak(),
                'maxStreak' => $user->getMaxStreak(),
                'completedChallenges' => $this->countCompletedChallenges($user),
                'isCurrentUser' => $user->getId() === $currentUser->getId(),
            ];
            $rank++;
        }
        
        // Dodaj statystyki aktualnego użytkownika
        $myStats = [
            'rank' => $currentUserRank,
            'points' => $currentUser->getTotalPoints(),
            'currentStreak' => $currentUser->getCurrentStreak(),
            'maxStreak' => $currentUser->getMaxStreak(),
            'completedChallenges' => $this->countCompletedChallenges($currentUser),
        ];
        
        return $this->json([
            'leaderboard' => $leaderboard,
            'myStats' => $myStats,
        ]);
    }

    /**
     * Check-in dzienny - aktualizuje streak
     */
    #[Route('/checkin', name: 'api_leaderboard_checkin', methods: ['POST'])]
    public function checkIn(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $previousStreak = $user->getCurrentStreak();
        $user->updateStreak();
        $newStreak = $user->getCurrentStreak();
        
        $this->entityManager->flush();
        
        $streakIncreased = $newStreak > $previousStreak;
        
        return $this->json([
            'message' => $streakIncreased ? '🔥 Streak kontynuowany!' : '✅ Już dzisiaj się zameldowałeś!',
            'currentStreak' => $newStreak,
            'maxStreak' => $user->getMaxStreak(),
            'streakIncreased' => $streakIncreased,
        ]);
    }

    /**
     * Pobierz statystyki streak dla aktualnego użytkownika
     */
    #[Route('/streak', name: 'api_leaderboard_streak', methods: ['GET'])]
    public function getStreak(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        // Sprawdź czy streak nie wygasł (ostatnia aktywność > 1 dzień temu)
        $lastActivity = $user->getLastActivityDate();
        $streakActive = true;
        
        if ($lastActivity !== null) {
            $today = new \DateTimeImmutable('today');
            $daysDiff = (int) $today->diff($lastActivity)->days;
            $streakActive = $daysDiff <= 1;
        }
        
        return $this->json([
            'currentStreak' => $user->getCurrentStreak(),
            'maxStreak' => $user->getMaxStreak(),
            'lastActivityDate' => $lastActivity?->format('Y-m-d'),
            'streakActive' => $streakActive,
            'needsCheckIn' => $lastActivity === null || $lastActivity->format('Y-m-d') !== (new \DateTimeImmutable('today'))->format('Y-m-d'),
        ]);
    }

    private function countCompletedChallenges(User $user): int
    {
        $count = 0;
        foreach ($user->getUserChallenges() as $uc) {
            if ($uc->getStatus() === 'completed') {
                $count++;
            }
        }
        return $count;
    }
}
