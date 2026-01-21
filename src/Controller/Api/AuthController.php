<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Entity\User;
use App\Message\SendWelcomeEmailMessage;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * ============================================================
 * 🔐 AUTH CONTROLLER - Autoryzacja i rejestracja
 * ============================================================
 * 
 * Endpoint login jest obsługiwany przez json_login w security.yaml
 * Tutaj mamy tylko register (i ewentualnie refresh token)
 */
#[Route('/api/auth')]
class AuthController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private MessageBusInterface $messageBus,
    ) {}

    /**
     * Rejestracja nowego użytkownika
     * 
     * POST /api/auth/register
     * Body: { "email": "...", "password": "...", "firstName": "...", "lastName": "..." }
     */
    #[Route('/register', name: 'api_auth_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        // Sprawdź czy wszystkie pola są podane
        if (!isset($data['email'], $data['password'], $data['firstName'], $data['lastName'])) {
            return $this->json([
                'error' => 'Missing required fields',
                'required' => ['email', 'password', 'firstName', 'lastName']
            ], Response::HTTP_BAD_REQUEST);
        }
        
        // Sprawdź czy email już istnieje
        if ($this->userRepository->findOneByEmail($data['email'])) {
            return $this->json([
                'error' => 'Email already exists'
            ], Response::HTTP_CONFLICT);
        }
        
        // Utwórz użytkownika
        $user = new User();
        $user->setEmail($data['email']);
        $user->setFirstName($data['firstName']);
        $user->setLastName($data['lastName']);
        $user->setPassword(
            $this->passwordHasher->hashPassword($user, $data['password'])
        );
        
        // Walidacja
        $errors = $this->validator->validate($user);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[$error->getPropertyPath()] = $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], Response::HTTP_BAD_REQUEST);
        }
        
        // Zapisz użytkownika
        $this->userRepository->save($user, true);
        
        // Wyślij email powitalny przez kolejkę (asynchronicznie!)
        // To jest przykład użycia Symfony Messenger
        $this->messageBus->dispatch(new SendWelcomeEmailMessage($user->getId()));
        
        return $this->json([
            'message' => 'User registered successfully',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'firstName' => $user->getFirstName(),
                'lastName' => $user->getLastName(),
            ]
        ], Response::HTTP_CREATED);
    }

    /**
     * Endpoint logowania jest obsługiwany przez security.yaml (json_login)
     * Ta metoda nigdy nie zostanie wywołana, ale jest potrzebna dla routingu
     */
    #[Route('/login', name: 'api_auth_login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Obsługiwane przez Lexik JWT
        throw new \LogicException('This method should not be reached');
    }

    /**
     * Pobierz dane zalogowanego użytkownika
     */
    #[Route('/me', name: 'api_auth_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'firstName' => $user->getFirstName(),
            'lastName' => $user->getLastName(),
            'roles' => $user->getRoles(),
            'isAdmin' => $user->isAdmin(),
            'totalPoints' => $user->getTotalPoints(),
            'createdAt' => $user->getCreatedAt()?->format('c'),
        ]);
    }
}
