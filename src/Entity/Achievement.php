<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AchievementRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ============================================================
 * 🏆 ENCJA ACHIEVEMENT - Osiągnięcia/Odznaki
 * ============================================================
 * 
 * System gamifikacji - użytkownicy zdobywają odznaki za:
 * - Ukończenie pierwszego wyzwania
 * - Zdobycie X punktów
 * - Streak dni bez telefonu
 */
#[ORM\Entity(repositoryClass: AchievementRepository::class)]
#[ORM\Table(name: 'achievements')]
class Achievement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    /**
     * Ścieżka do ikony osiągnięcia (SVG lub PNG)
     */
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $icon = null;

    /**
     * Ile punktów potrzeba do zdobycia tej odznaki
     */
    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private int $pointsRequired = 0;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Użytkownicy którzy zdobyli to osiągnięcie
     */
    #[ORM\OneToMany(mappedBy: 'achievement', targetEntity: UserAchievement::class)]
    private Collection $userAchievements;

    public function __construct()
    {
        $this->userAchievements = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(?string $icon): static
    {
        $this->icon = $icon;
        return $this;
    }

    public function getPointsRequired(): int
    {
        return $this->pointsRequired;
    }

    public function setPointsRequired(int $pointsRequired): static
    {
        $this->pointsRequired = $pointsRequired;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return Collection<int, UserAchievement>
     */
    public function getUserAchievements(): Collection
    {
        return $this->userAchievements;
    }
}
