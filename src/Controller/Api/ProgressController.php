<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Repository\UserChallengeRepository;
use App\Repository\UserAchievementRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * API dla postępów użytkownika
 */
#[Route('/api/progress')]
#[IsGranted('ROLE_USER')]
class ProgressController extends AbstractController
{
    public function __construct(
        private UserChallengeRepository $userChallengeRepository,
        private UserAchievementRepository $userAchievementRepository,
    ) {}

    /**
     * Podsumowanie postępów użytkownika
     */
    #[Route('', name: 'api_progress_summary', methods: ['GET'])]
    public function summary(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $activeChallenges = $this->userChallengeRepository->findActiveForUser($user);
        $completedChallenges = $this->userChallengeRepository->findCompletedForUser($user);
        $achievements = $this->userAchievementRepository->findByUser($user);
        
        return $this->json([
            'totalPoints' => $user->getTotalPoints(),
            'activeChallenges' => count($activeChallenges),
            'completedChallenges' => count($completedChallenges),
            'achievements' => count($achievements),
            'challenges' => array_map(fn($uc) => [
                'id' => $uc->getChallenge()?->getId(),
                'title' => $uc->getChallenge()?->getTitle(),
                'status' => $uc->getStatus(),
                'progress' => $uc->getProgress(),
                'remainingDays' => $uc->getRemainingDays(),
            ], $activeChallenges),
        ]);
    }

    /**
     * Historia wyzwań użytkownika
     */
    #[Route('/history', name: 'api_progress_history', methods: ['GET'])]
    public function history(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $allChallenges = $this->userChallengeRepository->findAllForUser($user);
        
        return $this->json(array_map(fn($uc) => [
            'id' => $uc->getId(),
            'challenge' => [
                'id' => $uc->getChallenge()?->getId(),
                'title' => $uc->getChallenge()?->getTitle(),
                'points' => $uc->getChallenge()?->getPoints(),
            ],
            'status' => $uc->getStatus(),
            'progress' => $uc->getProgress(),
            'startDate' => $uc->getStartDate()?->format('c'),
            'endDate' => $uc->getEndDate()?->format('c'),
        ], $allChallenges));
    }
}
