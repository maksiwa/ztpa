<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\SendWelcomeEmailMessage;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mime\Email;

/**
 * ============================================================
 * 📨 MESSAGE HANDLER - Obsługuje wysyłanie emaila powitalnego
 * ============================================================
 * 
 * CZYM JEST HANDLER?
 * 
 * Handler to klasa wykonująca rzeczywistą pracę.
 * Gdy Message trafi do kolejki i Worker go pobierze,
 * wywoływana jest metoda __invoke() Handlera.
 * 
 * ATRYBUT #[AsMessageHandler]:
 * - Rejestruje tę klasę jako handler dla SendWelcomeEmailMessage
 * - Symfony automatycznie dopasowuje Message do Handlera
 *   na podstawie typu parametru w __invoke()
 * 
 * ZALETY ASYNCHRONICZNEGO PRZETWARZANIA:
 * 1. Request kończy się natychmiast (user nie czeka)
 * 2. Możliwość retry przy błędach
 * 3. Możliwość skalowania (wiele workerów)
 * 4. Odporna na awarie (kolejka przetrwa restart)
 */
#[AsMessageHandler]
final class SendWelcomeEmailMessageHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private MailerInterface $mailer,
        private LoggerInterface $logger,
    ) {}

    /**
     * Ta metoda jest wywoływana przez Worker gdy przetwarza Message
     */
    public function __invoke(SendWelcomeEmailMessage $message): void
    {
        $userId = $message->getUserId();
        
        $this->logger->info('Processing welcome email for user', ['userId' => $userId]);
        
        // Pobierz użytkownika z bazy (świeże dane!)
        $user = $this->userRepository->find($userId);
        
        if (!$user) {
            $this->logger->warning('User not found, skipping welcome email', ['userId' => $userId]);
            return;
        }
        
        // Zbuduj email
        $email = (new Email())
            ->from('noreply@cichychallenge.pl')
            ->to($user->getEmail())
            ->subject('Witaj w Cichy Challenge! 🧘')
            ->html($this->buildEmailContent($user->getFirstName()));
        
        // Wyślij email
        try {
            $this->mailer->send($email);
            $this->logger->info('Welcome email sent successfully', [
                'userId' => $userId,
                'email' => $user->getEmail()
            ]);
        } catch (\Throwable $e) {
            $this->logger->error('Failed to send welcome email', [
                'userId' => $userId,
                'error' => $e->getMessage()
            ]);
            // Rzuć wyjątek żeby Messenger mógł retry
            throw $e;
        }
    }

    private function buildEmailContent(string $firstName): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: 'Segoe UI', sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #667eea; color: white; padding: 12px 24px; text-decoration: none; border-radius: 5px; margin-top: 20px; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <h1>🧘 Cichy Challenge</h1>
                </div>
                <div class="content">
                    <h2>Cześć {$firstName}!</h2>
                    <p>Dziękujemy za dołączenie do Cichy Challenge - platformy wspierającej cyfrowy detoks.</p>
                    <p>Możesz teraz:</p>
                    <ul>
                        <li>🎯 Dołączyć do wyzwań cyfrowego detoksu</li>
                        <li>📊 Śledzić swoje postępy</li>
                        <li>🏆 Zdobywać osiągnięcia</li>
                    </ul>
                    <p>Gotowy na pierwszy krok?</p>
                    <a href="http://localhost:5173/challenges" class="button">Zobacz wyzwania</a>
                    <p style="margin-top: 30px; color: #666;">Pozdrawiamy,<br>Zespół Cichy Challenge</p>
                </div>
            </div>
        </body>
        </html>
        HTML;
    }
}
