<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ActivityLogRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * ============================================================
 * 📝 ENCJA ACTIVITY_LOG - Logi aktywności
 * ============================================================
 * 
 * Przechowuje historię ważnych akcji użytkowników:
 * - Logowanie
 * - Dołączenie do wyzwania
 * - Ukończenie wyzwania
 * - Zdobycie osiągnięcia
 * 
 * WAŻNE: Ten log jest idealny do przetwarzania przez KOLEJKĘ!
 * Zamiast zapisywać synchronicznie (spowalnia request),
 * wysyłamy wiadomość do Messengera.
 */
#[ORM\Entity(repositoryClass: ActivityLogRepository::class)]
#[ORM\Table(name: 'activity_logs')]
#[ORM\Index(columns: ['user_id'], name: 'idx_activity_user')]
#[ORM\Index(columns: ['action'], name: 'idx_activity_action')]
#[ORM\Index(columns: ['created_at'], name: 'idx_activity_created')]
class ActivityLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Użytkownik który wykonał akcję
     * Nullable = true, bo akcje mogą być anonimowe (np. nieudane logowanie)
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'activityLogs')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    /**
     * Typ akcji (login, logout, join_challenge, complete_challenge, etc.)
     */
    #[ORM\Column(length: 100)]
    private ?string $action = null;

    /**
     * Dodatkowe dane jako JSON
     * Przykład: {"challenge_id": 1, "challenge_title": "24h bez telefonu"}
     */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $details = null;

    /**
     * Adres IP użytkownika (dla bezpieczeństwa)
     */
    #[ORM\Column(length: 45, nullable: true)]
    private ?string $ipAddress = null;

    /**
     * User Agent (przeglądarka/urządzenie)
     */
    #[ORM\Column(length: 500, nullable: true)]
    private ?string $userAgent = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    // Gettery i settery

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;
        return $this;
    }

    public function getDetails(): ?array
    {
        return $this->details;
    }

    public function setDetails(?array $details): static
    {
        $this->details = $details;
        return $this;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function setIpAddress(?string $ipAddress): static
    {
        $this->ipAddress = $ipAddress;
        return $this;
    }

    public function getUserAgent(): ?string
    {
        return $this->userAgent;
    }

    public function setUserAgent(?string $userAgent): static
    {
        $this->userAgent = $userAgent;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }
}
