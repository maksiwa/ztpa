<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserChallengeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * ============================================================
 * 🔗 ENCJA USER_CHALLENGE - Tabela łącząca (pivot table)
 * ============================================================
 * 
 * To jest TABELA ŁĄCZĄCA (junction/pivot table) pomiędzy User a Challenge.
 * 
 * Dlaczego osobna encja zamiast ManyToMany?
 * Bo przechowujemy DODATKOWE DANE o relacji:
 * - start_date (kiedy użytkownik dołączył)
 * - status (in_progress, completed, failed)
 * - progress (0-100%)
 * 
 * W czystym ManyToMany nie można przechowywać dodatkowych danych!
 */
#[ORM\Entity(repositoryClass: UserChallengeRepository::class)]
#[ORM\Table(name: 'user_challenges')]
#[ORM\UniqueConstraint(name: 'unique_user_challenge', columns: ['user_id', 'challenge_id'])]
class UserChallenge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Relacja ManyToOne - wiele UserChallenge należy do jednego User
     * 
     * JoinColumn: nazwa kolumny w bazie danych
     * onDelete: co zrobić gdy User zostanie usunięty (CASCADE = usuń też)
     */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userChallenges')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?User $user = null;

    /**
     * Relacja ManyToOne - wiele UserChallenge należy do jednego Challenge
     */
    #[ORM\ManyToOne(targetEntity: Challenge::class, inversedBy: 'userChallenges')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Challenge $challenge = null;

    /**
     * Data dołączenia do wyzwania
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $startDate = null;

    /**
     * Planowana data zakończenia (startDate + durationDays)
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $endDate = null;

    /**
     * Status wyzwania
     * - in_progress: w trakcie
     * - completed: ukończone
     * - failed: nieudane/porzucone
     */
    #[ORM\Column(length: 20)]
    private string $status = 'in_progress';

    /**
     * Postęp 0-100%
     */
    #[ORM\Column]
    private int $progress = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->startDate = new \DateTimeImmutable();
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

    public function getChallenge(): ?Challenge
    {
        return $this->challenge;
    }

    public function setChallenge(?Challenge $challenge): static
    {
        $this->challenge = $challenge;
        
        // Automatycznie oblicz datę zakończenia
        if ($challenge !== null && $this->startDate !== null) {
            $this->endDate = $this->startDate->modify('+' . $challenge->getDurationDays() . ' days');
        }
        
        return $this;
    }

    public function getStartDate(): ?\DateTimeImmutable
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeImmutable $startDate): static
    {
        $this->startDate = $startDate;
        return $this;
    }

    public function getEndDate(): ?\DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getProgress(): int
    {
        return $this->progress;
    }

    public function setProgress(int $progress): static
    {
        // Ograniczenie do 0-100
        $this->progress = max(0, min(100, $progress));
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    // ─────────────────────────────────────────────
    // HELPER METHODS
    // ─────────────────────────────────────────────

    /**
     * Oblicza ile dni pozostało do końca wyzwania
     */
    public function getRemainingDays(): int
    {
        if ($this->endDate === null) {
            return 0;
        }

        $now = new \DateTimeImmutable();
        $diff = $now->diff($this->endDate);
        
        return $diff->invert ? 0 : $diff->days;
    }

    /**
     * Sprawdza czy wyzwanie jest aktywne
     */
    public function isActive(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Oznacza wyzwanie jako ukończone
     */
    public function markAsCompleted(): static
    {
        $this->status = 'completed';
        $this->progress = 100;
        return $this;
    }

    /**
     * Oznacza wyzwanie jako nieudane
     */
    public function markAsFailed(): static
    {
        $this->status = 'failed';
        return $this;
    }
}
