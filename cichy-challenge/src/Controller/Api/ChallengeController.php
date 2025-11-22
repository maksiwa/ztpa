<?php

namespace App\Controller\Api;

use App\Repository\ChallengeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/challenges', name: 'api_challenges_')]
class ChallengeController extends AbstractController
{
    public function __construct(
        private ChallengeRepository $challengeRepository,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $challenges = $this->challengeRepository->findAll();

        $data = $this->serializer->serialize($challenges, 'json', [
            'groups' => ['challenge:read'],
        ]);

        return new JsonResponse(
            json_decode($data, true),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }

    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(string $id): JsonResponse
    {
        // Validate that id is a positive integer
        if (!ctype_digit($id) || (int)$id <= 0) {
            return new JsonResponse(
                [
                    'error' => 'Bad Request',
                    'message' => 'Invalid challenge ID. ID must be a positive integer.',
                ],
                Response::HTTP_BAD_REQUEST,
                ['Content-Type' => 'application/json']
            );
        }

        $challengeId = (int)$id;
        $challenge = $this->challengeRepository->find($challengeId);

        if (!$challenge) {
            return new JsonResponse(
                [
                    'error' => 'Not Found',
                    'message' => 'Challenge not found.',
                ],
                Response::HTTP_NOT_FOUND,
                ['Content-Type' => 'application/json']
            );
        }

        $data = $this->serializer->serialize($challenge, 'json', [
            'groups' => ['challenge:read'],
        ]);

        return new JsonResponse(
            json_decode($data, true),
            Response::HTTP_OK,
            ['Content-Type' => 'application/json']
        );
    }
}

