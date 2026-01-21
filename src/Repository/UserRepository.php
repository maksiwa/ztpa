<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * ============================================================
 * 📦 REPOZYTORIUM USER
 * ============================================================
 * 
 * CZYM JEST REPOZYTORIUM?
 * 
 * Repozytorium to "magazyn" obiektów. Odpowiada za:
 * - Pobieranie obiektów z bazy danych
 * - Zapisywanie obiektów do bazy
 * - Niestandardowe zapytania
 * 
 * DLACZEGO NIE PISZEMY SQL BEZPOŚREDNIO?
 * 
 * 1. ABSTRAKCJA - Kontroler nie musi znać struktury bazy
 * 2. TESTOWALNOŚĆ - Łatwo podmienić na mock
 * 3. REUŻYWALNOŚĆ - Jedno miejsce dla logiki zapytań
 * 4. DRY - Don't Repeat Yourself
 * 
 * PORÓWNANIE Z TWOIM STARYM KODEM:
 * 
 * Stary:
 *   $stmt = $db->prepare('SELECT * FROM users WHERE email = :email');
 *   $stmt->execute([':email' => $email]);
 *   $data = $stmt->fetch();
 *   $user = new User(...);
 * 
 * Doctrine:
 *   $user = $this->userRepository->findOneByEmail($email);
 *   // Gotowe! Doctrine automatycznie mapuje na obiekt
 * 
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Zapisuje użytkownika do bazy danych
     * 
     * @param bool $flush - czy od razu wykonać INSERT/UPDATE
     *                      (false = dodaj do "paczki", wykonaj później)
     */
    public function save(User $entity, bool $flush = false): void
    {
        // persist() = "zapamiętaj ten obiekt, będziesz go zapisywać"
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            // flush() = "teraz wykonaj wszystkie zapamiętane operacje"
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Usuwa użytkownika z bazy
     */
    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Wymagane przez PasswordUpgraderInterface
     * 
     * Symfony może automatycznie aktualizować hash hasła
     * gdy algorytm hashowania się zmieni
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->save($user, true);
    }

    /**
     * Znajdź użytkownika po emailu
     * 
     * To samo co findOneBy(['email' => $email]), ale bardziej czytelne
     */
    public function findOneByEmail(string $email): ?User
    {
        return $this->findOneBy(['email' => $email]);
    }

    /**
     * Znajdź wszystkich aktywnych użytkowników
     * 
     * Przykład użycia QueryBuilder (bardziej zaawansowane zapytania)
     */
    public function findAllActive(): array
    {
        // QueryBuilder pozwala budować zapytania programistycznie
        return $this->createQueryBuilder('u')  // 'u' to alias dla tabeli users
            ->andWhere('u.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('u.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Znajdź użytkowników z rolą admina
     */
    public function findAdmins(): array
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.roles LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Statystyki użytkowników (dla panelu admina)
     */
    public function getStats(): array
    {
        $conn = $this->getEntityManager()->getConnection();
        
        // Czasami czyste SQL jest prostsze dla agregatów
        $sql = '
            SELECT 
                COUNT(*) as total,
                COUNT(*) FILTER (WHERE is_active = true) as active,
                COUNT(*) FILTER (WHERE is_active = false) as blocked,
                COUNT(*) FILTER (WHERE created_at > CURRENT_DATE - INTERVAL \'7 days\') as new_this_week
            FROM users
        ';
        
        return $conn->executeQuery($sql)->fetchAssociative();
    }
}
