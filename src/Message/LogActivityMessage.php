<?php

declare(strict_types=1);

namespace App\Message;

/**
 * Message do asynchronicznego logowania aktywności użytkownika
 */
final class LogActivityMessage
{
    public function __construct(
        private ?int $userId,
        private string $action,
        private ?array $details = null,
        private ?string $ipAddress = null,
        private ?string $userAgent = null,
    ) {}

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function getAction(): string
    {
        return $this->action;
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }
}
