<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Achievement;
use App\Entity\ActivityLog;
use App\Entity\Challenge;
use App\Entity\Quote;
use App\Entity\User;
use App\Entity\UserAchievement;
use App\Entity\UserChallenge;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Fixtures - generuje 75+ rekordów testowych
 * Uruchom: php bin/console doctrine:fixtures:load
 */
class AppFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // 1. UŻYTKOWNICY (10 rekordów)
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setFirstName('Admin');
        $admin->setLastName('System');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'Admin123!'));
        $manager->persist($admin);
        
        $users = [];
        $userNames = [
            ['Jan', 'Kowalski', 'jan@example.com'],
            ['Anna', 'Nowak', 'anna@example.com'],
            ['Piotr', 'Wiśniewski', 'piotr@example.com'],
            ['Maria', 'Wójcik', 'maria@example.com'],
            ['Tomasz', 'Kamiński', 'tomasz@example.com'],
            ['Katarzyna', 'Lewandowska', 'kasia@example.com'],
            ['Michał', 'Zieliński', 'michal@example.com'],
            ['Agnieszka', 'Szymańska', 'aga@example.com'],
            ['Paweł', 'Woźniak', 'pawel@example.com'],
        ];
        
        foreach ($userNames as [$firstName, $lastName, $email]) {
            $user = new User();
            $user->setEmail($email);
            $user->setFirstName($firstName);
            $user->setLastName($lastName);
            $user->setPassword($this->passwordHasher->hashPassword($user, 'User123!'));
            $manager->persist($user);
            $users[] = $user;
        }
        
        // 2. WYZWANIA (5 rekordów)
        $challengesData = [
            ['24h bez social media', 'Spędź jeden dzień bez sprawdzania social mediów.', 1, 'easy', 100],
            ['Weekend offline', 'Cały weekend bez internetu.', 2, 'medium', 250],
            ['Tydzień minimalizmu cyfrowego', 'Używaj telefonu tylko do niezbędnych rzeczy.', 7, 'hard', 500],
            ['Poranek bez ekranu', 'Nie dotykaj telefonu przez pierwszą godzinę.', 3, 'easy', 150],
            ['Wieczorny detoks', 'Odłóż telefon 2 godziny przed snem.', 5, 'medium', 300],
        ];
        
        $challenges = [];
        foreach ($challengesData as [$title, $description, $days, $difficulty, $points]) {
            $challenge = new Challenge();
            $challenge->setTitle($title);
            $challenge->setDescription($description);
            $challenge->setDurationDays($days);
            $challenge->setDifficultyLevel($difficulty);
            $challenge->setPoints($points);
            $manager->persist($challenge);
            $challenges[] = $challenge;
        }
        
        // 3. OSIĄGNIĘCIA (5 rekordów)
        $achievementsData = [
            ['Pierwszy krok', 'Ukończ swoje pierwsze wyzwanie', 'first-step.svg', 100],
            ['Cyfrowy wojownik', 'Zdobądź 500 punktów', 'warrior.svg', 500],
            ['Mistrz detoksu', 'Zdobądź 1000 punktów', 'master.svg', 1000],
            ['Tydzień mocy', 'Ukończ wyzwanie 7-dniowe', 'week-power.svg', 500],
            ['Minimalist', 'Ukończ 3 wyzwania', 'minimalist.svg', 300],
        ];
        
        $achievements = [];
        foreach ($achievementsData as [$name, $description, $icon, $pointsRequired]) {
            $achievement = new Achievement();
            $achievement->setName($name);
            $achievement->setDescription($description);
            $achievement->setIcon($icon);
            $achievement->setPointsRequired($pointsRequired);
            $manager->persist($achievement);
            $achievements[] = $achievement;
        }
        
        // 4. CYTATY (10 rekordów)
        $quotesData = [
            ['Offline is the new luxury.', 'Unknown', 'digital-detox'],
            ['Life is what happens when your smartphone is down.', 'Modern Wisdom', 'digital-detox'],
            ['Disconnect to reconnect.', 'Unknown', 'digital-detox'],
            ['Your mind needs quiet time.', 'Wellness Expert', 'mindfulness'],
            ['Cicha rewolucja zaczyna się od wyłączenia powiadomień.', 'Jan Kowalski', 'motivation'],
            ['Nie musisz być online, żeby być obecny.', 'Anna Nowak', 'mindfulness'],
            ['Twój czas jest cenniejszy niż każdy like.', 'Digital Coach', 'motivation'],
            ['Zamiast scrollować, oddychaj.', 'Wellness Expert', 'mindfulness'],
            ['Real life has no filters.', 'Unknown', 'digital-detox'],
            ['Be where your feet are.', 'Unknown', 'mindfulness'],
        ];
        
        foreach ($quotesData as [$content, $author, $category]) {
            $quote = new Quote();
            $quote->setContent($content);
            $quote->setAuthor($author);
            $quote->setCategory($category);
            $manager->persist($quote);
        }
        
        // 5. PRZYPISANIA DO WYZWAŃ (15 rekordów)
        $statuses = ['completed', 'in_progress', 'failed'];
        foreach ($users as $index => $user) {
            $numChallenges = ($index % 2) + 1;
            for ($i = 0; $i < $numChallenges; $i++) {
                $challenge = $challenges[($index + $i) % count($challenges)];
                $userChallenge = new UserChallenge();
                $userChallenge->setUser($user);
                $userChallenge->setChallenge($challenge);
                $userChallenge->setStatus($statuses[$index % 3]);
                $userChallenge->setProgress($statuses[$index % 3] === 'completed' ? 100 : random_int(20, 80));
                $manager->persist($userChallenge);
            }
        }
        
        // 6. ZDOBYTE OSIĄGNIĘCIA (10 rekordów)
        for ($i = 0; $i < 10; $i++) {
            $userAchievement = new UserAchievement();
            $userAchievement->setUser($users[$i % count($users)]);
            $userAchievement->setAchievement($achievements[$i % count($achievements)]);
            $manager->persist($userAchievement);
        }
        
        // 7. LOGI AKTYWNOŚCI (20 rekordów)
        $actions = ['login', 'logout', 'join_challenge', 'complete_challenge', 'earn_achievement'];
        for ($i = 0; $i < 20; $i++) {
            $log = new ActivityLog();
            $log->setUser($users[$i % count($users)]);
            $log->setAction($actions[$i % count($actions)]);
            $log->setIpAddress('192.168.1.' . ($i + 1));
            $manager->persist($log);
        }
        
        $manager->flush();
        // Total: 10 + 5 + 5 + 10 + 15 + 10 + 20 = 75 rekordów
    }
}
