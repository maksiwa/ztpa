<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Entity\UserChallenge;
use App\Repository\ChallengeRepository;
use App\Repository\UserChallengeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * API dla wyzwań cyfrowego detoksu
 */
#[Route('/api/challenges')]
#[IsGranted('ROLE_USER')]
class ChallengeController extends AbstractController
{
    public function __construct(
        private ChallengeRepository $challengeRepository,
        private UserChallengeRepository $userChallengeRepository,
    ) {}

    /**
     * Lista wszystkich wyzwań
     */
    #[Route('', name: 'api_challenges_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $challenges = $this->challengeRepository->findAllSortedByDifficulty();
        
        /** @var User $user */
        $user = $this->getUser();
        
        $data = [];
        foreach ($challenges as $challenge) {
            $userChallenge = $this->userChallengeRepository->findOneBy([
                'user' => $user,
                'challenge' => $challenge,
                'status' => 'in_progress'
            ]);
            
            $data[] = [
                'id' => $challenge->getId(),
                'title' => $challenge->getTitle(),
                'description' => $challenge->getDescription(),
                'durationDays' => $challenge->getDurationDays(),
                'difficultyLevel' => $challenge->getDifficultyLevel(),
                'points' => $challenge->getPoints(),
                'participantsCount' => $challenge->getParticipantsCount(),
                'isJoined' => $userChallenge !== null,
                'progress' => $userChallenge?->getProgress(),
            ];
        }
        
        return $this->json($data);
    }

    /**
     * Szczegóły wyzwania
     */
    #[Route('/{id}', name: 'api_challenges_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $challenge = $this->challengeRepository->find($id);
        
        if (!$challenge) {
            return $this->json(['error' => 'Challenge not found'], Response::HTTP_NOT_FOUND);
        }
        
        return $this->json([
            'id' => $challenge->getId(),
            'title' => $challenge->getTitle(),
            'description' => $challenge->getDescription(),
            'durationDays' => $challenge->getDurationDays(),
            'difficultyLevel' => $challenge->getDifficultyLevel(),
            'points' => $challenge->getPoints(),
            'participantsCount' => $challenge->getParticipantsCount(),
        ]);
    }

    /**
     * Dołącz do wyzwania
     */
    #[Route('/{id}/join', name: 'api_challenges_join', methods: ['POST'])]
    public function join(int $id): JsonResponse
    {
        $challenge = $this->challengeRepository->find($id);
        
        if (!$challenge) {
            return $this->json(['error' => 'Challenge not found'], Response::HTTP_NOT_FOUND);
        }
        
        /** @var User $user */
        $user = $this->getUser();
        
        // Sprawdź czy już uczestniczy
        if ($this->userChallengeRepository->isUserParticipating($user, $id)) {
            return $this->json(['error' => 'Already participating in this challenge'], Response::HTTP_CONFLICT);
        }
        
        // Dołącz do wyzwania
        $userChallenge = new UserChallenge();
        $userChallenge->setUser($user);
        $userChallenge->setChallenge($challenge);
        
        $this->userChallengeRepository->save($userChallenge, true);
        
        return $this->json([
            'message' => 'Successfully joined the challenge',
            'challenge' => $challenge->getTitle(),
            'endsAt' => $userChallenge->getEndDate()?->format('c'),
        ], Response::HTTP_CREATED);
    }

    /**
     * Opuść wyzwanie
     */
    #[Route('/{id}/leave', name: 'api_challenges_leave', methods: ['POST'])]
    public function leave(int $id): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $userChallenge = $this->userChallengeRepository->findOneBy([
            'user' => $user,
            'challenge' => $id,
            'status' => 'in_progress'
        ]);
        
        if (!$userChallenge) {
            return $this->json(['error' => 'Not participating in this challenge'], Response::HTTP_NOT_FOUND);
        }
        
        $userChallenge->markAsFailed();
        $this->userChallengeRepository->save($userChallenge, true);
        
        return $this->json(['message' => 'Left the challenge']);
    }

    /**
     * Oznacz wyzwanie jako ukończone
     */
    #[Route('/{id}/complete', name: 'api_challenges_complete', methods: ['POST'])]
    public function complete(int $id): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        $userChallenge = $this->userChallengeRepository->findOneBy([
            'user' => $user,
            'challenge' => $id,
            'status' => 'in_progress'
        ]);
        
        if (!$userChallenge) {
            return $this->json(['error' => 'Not participating in this challenge'], Response::HTTP_NOT_FOUND);
        }
        
        $userChallenge->markAsCompleted();
        $this->userChallengeRepository->save($userChallenge, true);
        
        return $this->json([
            'message' => 'Challenge completed!',
            'points' => $userChallenge->getChallenge()?->getPoints(),
        ]);
    }
}
