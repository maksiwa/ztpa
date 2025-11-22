<?php

namespace App\Command;

use App\Entity\Challenge;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:add-sample-challenges',
    description: 'Add sample challenges to the database',
)]
class AddSampleChallengesCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $challenges = [
            [
                'title' => '24 godziny bez social mediów',
                'description' => 'Wyzwanie polegające na całkowitym odcięciu się od mediów społecznościowych na 24 godziny.',
                'duration' => 1,
                'difficulty' => 'easy',
            ],
            [
                'title' => 'Tydzień minimalizmu cyfrowego',
                'description' => 'Przez 7 dni używaj tylko niezbędnych aplikacji i urządzeń cyfrowych.',
                'duration' => 7,
                'difficulty' => 'medium',
            ],
            [
                'title' => 'Miesiąc bez smartfona wieczorem',
                'description' => 'Przez 30 dni nie używaj smartfona po godzinie 20:00.',
                'duration' => 30,
                'difficulty' => 'hard',
            ],
            [
                'title' => 'Weekend bez ekranów',
                'description' => 'Spędź cały weekend bez żadnych ekranów - telefon, komputer, TV.',
                'duration' => 2,
                'difficulty' => 'medium',
            ],
        ];

        foreach ($challenges as $challengeData) {
            $challenge = new Challenge();
            $challenge->setTitle($challengeData['title']);
            $challenge->setDescription($challengeData['description']);
            $challenge->setDuration($challengeData['duration']);
            $challenge->setDifficulty($challengeData['difficulty']);
            $challenge->setIsActive(true);

            $this->entityManager->persist($challenge);
        }

        $this->entityManager->flush();

        $io->success(sprintf('Dodano %d przykładowych wyzwań!', count($challenges)));

        return Command::SUCCESS;
    }
}
