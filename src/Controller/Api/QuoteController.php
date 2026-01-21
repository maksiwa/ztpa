<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Repository\QuoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * API dla cytatów motywacyjnych
 */
#[Route('/api/quotes')]
#[IsGranted('ROLE_USER')]
class QuoteController extends AbstractController
{
    public function __construct(
        private QuoteRepository $quoteRepository,
    ) {}

    /**
     * Pobierz losowy cytat
     */
    #[Route('/random', name: 'api_quotes_random', methods: ['GET'])]
    public function random(): JsonResponse
    {
        $quote = $this->quoteRepository->findRandom();
        
        if (!$quote) {
            return $this->json(['content' => 'No quotes available', 'author' => null]);
        }
        
        return $this->json([
            'id' => $quote->getId(),
            'content' => $quote->getContent(),
            'author' => $quote->getAuthor(),
            'category' => $quote->getCategory(),
        ]);
    }

    /**
     * Lista wszystkich cytatów
     */
    #[Route('', name: 'api_quotes_list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $quotes = $this->quoteRepository->findAll();
        
        return $this->json(array_map(fn($q) => [
            'id' => $q->getId(),
            'content' => $q->getContent(),
            'author' => $q->getAuthor(),
            'category' => $q->getCategory(),
        ], $quotes));
    }
}
