<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\UserRepository;
use App\Repository\ChallengeRepository;
use App\Repository\ActivityLogRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use OpenApi\Attributes as OA;

/**
 * API dla administratorów
 */
#[Route('/api/admin')]
#[IsGranted('ROLE_ADMIN')]
#[OA\Tag(name: 'Admin', description: 'Admin-only endpoints')]
class AdminController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private ChallengeRepository $challengeRepository,
        private ActivityLogRepository $activityLogRepository,
    ) {}

    /**
     * Dashboard statistics
     */
    #[Route('/stats', name: 'api_admin_stats', methods: ['GET'])]
    #[OA\Get(summary: 'Get admin dashboard statistics')]
    public function stats(): JsonResponse
    {
        $userStats = $this->userRepository->getStats();
        
        return $this->json([
            'users' => $userStats,
            'challenges' => [
                'total' => count($this->challengeRepository->findAll()),
            ],
        ]);
    }

    /**
     * Pobierz wszystkich użytkowników
     */
    #[Route('/users', name: 'api_admin_users', methods: ['GET'])]
    #[OA\Get(summary: 'List all users')]
    public function users(): JsonResponse
    {
        $users = $this->userRepository->findAll();
        
        return $this->json(array_map(fn($u) => [
            'id' => $u->getId(),
            'email' => $u->getEmail(),
            'firstName' => $u->getFirstName(),
            'lastName' => $u->getLastName(),
            'roles' => $u->getRoles(),
            'isActive' => $u->isActive(),
            'createdAt' => $u->getCreatedAt()?->format('c'),
        ], $users));
    }

    /**
     * Pobierz ostatnie logi aktywności
     */
    #[Route('/logs', name: 'api_admin_logs', methods: ['GET'])]
    #[OA\Get(summary: 'Get recent activity logs')]
    public function logs(): JsonResponse
    {
        $logs = $this->activityLogRepository->findRecent(50);
        
        return $this->json(array_map(fn($l) => [
            'id' => $l->getId(),
            'user' => $l->getUser()?->getEmail(),
            'action' => $l->getAction(),
            'ip' => $l->getIpAddress(),
            'createdAt' => $l->getCreatedAt()?->format('c'),
        ], $logs));
    }

    /**
     * Blokuj/odblokuj użytkownika
     */
    #[Route('/users/{id}/toggle', name: 'api_admin_toggle_user', methods: ['POST'])]
    #[OA\Post(summary: 'Toggle user active status')]
    public function toggleUser(int $id): JsonResponse
    {
        $user = $this->userRepository->find($id);
        
        if (!$user) {
            return $this->json(['error' => 'User not found'], 404);
        }
        
        $user->setIsActive(!$user->isActive());
        $this->userRepository->save($user, true);
        
        return $this->json([
            'message' => $user->isActive() ? 'User activated' : 'User blocked',
            'isActive' => $user->isActive(),
        ]);
    }
}
