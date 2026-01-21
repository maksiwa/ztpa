<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ChallengeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ============================================================
 * 🎯 ENCJA CHALLENGE - Wyzwania cyfrowego detoksu
 * ============================================================
 * 
 * Ta encja przechowuje definicje wyzwań, np:
 * - "24h bez social media"
 * - "Tydzień minimalizmu cyfrowego"
 * 
 * Jeden Challenge może mieć wielu uczestników (przez UserChallenge)
 */
#[ORM\Entity(repositoryClass: ChallengeRepository::class)]
#[ORM\Table(name: 'challenges')]
class Challenge
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 5, max: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $description = null;

    /**
     * Czas trwania wyzwania w dniach
     */
    #[ORM\Column]
    #[Assert\Positive]
    private int $durationDays = 1;

    /**
     * Poziom trudności - enum-like z ograniczonymi wartościami
     * W prawdziwym projekcie użyłbyś PHP 8.1 Enum
     */
    #[ORM\Column(length: 20)]
    #[Assert\Choice(choices: ['easy', 'medium', 'hard'])]
    private string $difficultyLevel = 'easy';

    /**
     * Punkty za ukończenie wyzwania
     */
    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $points = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Relacja odwrotna - wszyscy uczestnicy tego wyzwania
     */
    #[ORM\OneToMany(mappedBy: 'challenge', targetEntity: UserChallenge::class)]
    private Collection $userChallenges;

    public function __construct()
    {
        $this->userChallenges = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    // Gettery i settery

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getDurationDays(): int
    {
        return $this->durationDays;
    }

    public function setDurationDays(int $durationDays): static
    {
        $this->durationDays = $durationDays;
        return $this;
    }

    public function getDifficultyLevel(): string
    {
        return $this->difficultyLevel;
    }

    public function setDifficultyLevel(string $difficultyLevel): static
    {
        $this->difficultyLevel = $difficultyLevel;
        return $this;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function setPoints(int $points): static
    {
        $this->points = $points;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, UserChallenge>
     */
    public function getUserChallenges(): Collection
    {
        return $this->userChallenges;
    }

    /**
     * Liczba aktywnych uczestników
     */
    public function getParticipantsCount(): int
    {
        return $this->userChallenges->count();
    }
}
