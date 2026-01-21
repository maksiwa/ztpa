<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Quote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quote>
 */
class QuoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quote::class);
    }

    public function save(Quote $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Pobierz losowy cytat
     * 
     * PostgreSQL: ORDER BY RANDOM()
     */
    public function findRandom(): ?Quote
    {
        $count = $this->count([]);
        if ($count === 0) {
            return null;
        }

        return $this->createQueryBuilder('q')
            ->setFirstResult(random_int(0, $count - 1))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Pobierz losowy cytat z kategorii
     */
    public function findRandomByCategory(string $category): ?Quote
    {
        $quotes = $this->findBy(['category' => $category]);
        if (empty($quotes)) {
            return null;
        }
        return $quotes[array_rand($quotes)];
    }
}
