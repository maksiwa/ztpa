<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * ============================================================
 * 👤 ENCJA USER - Reprezentuje tabelę 'users' w bazie danych
 * ============================================================
 * 
 * WAŻNE KONCEPTY:
 * 
 * 1. ATRYBUTY PHP 8 (#[...])
 *    - Zastępują stare adnotacje /** @ORM\Entity 
 *    - Są częścią języka PHP, nie tylko komentarzami
 * 
 * 2. ORM\Entity - oznacza "ta klasa to encja bazy danych"
 *    - repositoryClass: klasa do pobierania danych
 * 
 * 3. UserInterface - interfejs wymagany przez Symfony Security
 *    - Definiuje metody: getUserIdentifier(), getRoles(), getPassword()
 * 
 * 4. PasswordAuthenticatedUserInterface - dla sprawdzania hasła
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'users')]  // Nazwa tabeli w bazie
#[ORM\HasLifecycleCallbacks]  // Włącza metody typu @PrePersist
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    // ─────────────────────────────────────────────
    // POLA (Kolumny w bazie danych)
    // ─────────────────────────────────────────────

    /**
     * ID - klucz główny, automatycznie generowany
     * 
     * ORM\Id - oznacza klucz główny
     * ORM\GeneratedValue - automatyczna numeracja (SERIAL w PostgreSQL)
     * ORM\Column - mapowanie na kolumnę
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Email - unikalny, służy jako login
     * 
     * Assert\Email - walidacja formatu email
     * Assert\NotBlank - nie może być pusty
     */
    #[ORM\Column(length: 180, unique: true)]
    #[Assert\NotBlank(message: 'Email jest wymagany')]
    #[Assert\Email(message: 'Nieprawidłowy format email')]
    private ?string $email = null;

    /**
     * Role użytkownika - przechowywane jako JSON
     * 
     * Przykład: ["ROLE_USER"] lub ["ROLE_USER", "ROLE_ADMIN"]
     * Symfony Security używa ról do kontroli dostępu
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * Hash hasła - NIGDY nie przechowujemy hasła w plaintext!
     * 
     * Symfony używa password_hash() do hashowania
     */
    #[ORM\Column]
    private ?string $password = null;

    /**
     * Imię użytkownika
     */
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Imię jest wymagane')]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $firstName = null;

    /**
     * Nazwisko użytkownika
     */
    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: 'Nazwisko jest wymagane')]
    #[Assert\Length(min: 2, max: 100)]
    private ?string $lastName = null;

    /**
     * Czy konto jest aktywne (możliwość blokowania przez admina)
     */
    #[ORM\Column]
    private bool $isActive = true;

    /**
     * Data utworzenia konta
     * 
     * Types::DATETIME_IMMUTABLE - niezmienialny datetime
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * Data ostatniej aktualizacji
     */
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    // ─────────────────────────────────────────────
    // RELACJE (Powiązania z innymi encjami)
    // ─────────────────────────────────────────────

    /**
     * Wyzwania użytkownika - relacja jeden-do-wielu
     * 
     * OneToMany = jeden User ma wiele UserChallenge
     * mappedBy = pole w UserChallenge które wskazuje na User
     * cascade = operacje propagują się (persist, remove)
     * orphanRemoval = usuń sieroty (UserChallenge bez User)
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserChallenge::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $userChallenges;

    /**
     * Osiągnięcia użytkownika
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserAchievement::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $userAchievements;

    /**
     * Logi aktywności użytkownika
     */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: ActivityLog::class, cascade: ['persist', 'remove'])]
    private Collection $activityLogs;

    // ─────────────────────────────────────────────
    // KONSTRUKTOR
    // ─────────────────────────────────────────────

    public function __construct()
    {
        // Inicjalizacja kolekcji (wymagane przez Doctrine)
        $this->userChallenges = new ArrayCollection();
        $this->userAchievements = new ArrayCollection();
        $this->activityLogs = new ArrayCollection();
        
        // Ustaw datę utworzenia
        $this->createdAt = new \DateTimeImmutable();
    }

    // ─────────────────────────────────────────────
    // LIFECYCLE CALLBACKS
    // ─────────────────────────────────────────────
    
    /**
     * Wywoływane automatycznie przed zapisem do bazy (UPDATE)
     */
    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    // ─────────────────────────────────────────────
    // GETTERY I SETTERY
    // ─────────────────────────────────────────────

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Wymagane przez UserInterface
     * Identyfikator używany do logowania
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Wymagane przez UserInterface
     * Zwraca role użytkownika (zawsze dodaje ROLE_USER)
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // Każdy użytkownik ma przynajmniej ROLE_USER
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * Wymagane przez PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Wymagane przez UserInterface
     * Używane do czyszczenia wrażliwych danych po autentykacji
     */
    public function eraseCredentials(): void
    {
        // Jeśli przechowujesz tymczasowe dane (np. plainPassword), wyczyść je tutaj
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Helper - pełne imię i nazwisko
     */
    public function getFullName(): string
    {
        return $this->firstName . ' ' . $this->lastName;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;
        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // ─────────────────────────────────────────────
    // METODY DLA KOLEKCJI (Relacji)
    // ─────────────────────────────────────────────

    /**
     * @return Collection<int, UserChallenge>
     */
    public function getUserChallenges(): Collection
    {
        return $this->userChallenges;
    }

    public function addUserChallenge(UserChallenge $userChallenge): static
    {
        if (!$this->userChallenges->contains($userChallenge)) {
            $this->userChallenges->add($userChallenge);
            $userChallenge->setUser($this);
        }
        return $this;
    }

    public function removeUserChallenge(UserChallenge $userChallenge): static
    {
        if ($this->userChallenges->removeElement($userChallenge)) {
            if ($userChallenge->getUser() === $this) {
                $userChallenge->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, UserAchievement>
     */
    public function getUserAchievements(): Collection
    {
        return $this->userAchievements;
    }

    /**
     * @return Collection<int, ActivityLog>
     */
    public function getActivityLogs(): Collection
    {
        return $this->activityLogs;
    }

    // ─────────────────────────────────────────────
    // HELPER METHODS
    // ─────────────────────────────────────────────

    /**
     * Sprawdza czy użytkownik ma rolę admina
     */
    public function isAdmin(): bool
    {
        return in_array('ROLE_ADMIN', $this->getRoles(), true);
    }

    /**
     * Oblicza łączną liczbę punktów użytkownika
     */
    public function getTotalPoints(): int
    {
        $points = 0;
        foreach ($this->userChallenges as $userChallenge) {
            if ($userChallenge->getStatus() === 'completed') {
                $points += $userChallenge->getChallenge()?->getPoints() ?? 0;
            }
        }
        return $points;
    }
}
