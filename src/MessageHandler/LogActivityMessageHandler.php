<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\ActivityLog;
use App\Message\LogActivityMessage;
use App\Repository\ActivityLogRepository;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Handler do asynchronicznego logowania aktywności
 */
#[AsMessageHandler]
final class LogActivityMessageHandler
{
    public function __construct(
        private ActivityLogRepository $activityLogRepository,
        private UserRepository $userRepository,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(LogActivityMessage $message): void
    {
        $this->logger->info('Logging activity', [
            'action' => $message->getAction(),
            'userId' => $message->getUserId(),
        ]);

        $log = new ActivityLog();
        
        if ($message->getUserId()) {
            $user = $this->userRepository->find($message->getUserId());
            $log->setUser($user);
        }
        
        $log->setAction($message->getAction());
        $log->setDetails($message->getDetails());
        $log->setIpAddress($message->getIpAddress());
        $log->setUserAgent($message->getUserAgent());
        
        $this->activityLogRepository->save($log, true);
    }
}
