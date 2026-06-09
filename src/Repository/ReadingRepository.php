<?php

namespace App\Repository;

use App\Entity\Reading;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Reading>
 *
 * @method Reading|null find($id, $lockMode = null, $lockVersion = null)
 * @method Reading|null findOneBy(array<string, string> $criteria, array<string, string>|null $orderBy = null)
 * @method Reading[]    findAll()
 * @method Reading[]    findBy(array<string, string> $criteria, array<string, string>|null $orderBy = null, $limit = null, $offset = null)
 */
class ReadingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Reading::class);
    }

    public function findRecords(): \Doctrine\ORM\Query
    {
        return $this->createQueryBuilder('r')
            ->orderBy('r.date', 'DESC')
            ->getQuery();
    }

    /** @return array<array-key, mixed> */
    public function findLatestRecords(int $limit): array
    {
        $connection = $this->getEntityManager()->getConnection();

        return $connection->executeQuery(
            'SELECT
                    (reading.date::date) AS date,
                    MAX(reading.value) AS value,
                    MAX(reading.value) -  LEAD(MAX(reading.value)) OVER (ORDER BY date::date DESC) AS usage,
                    EXTRACT(EPOCH FROM (MAX(reading.date) -  LEAD(MAX(reading.date)) OVER (ORDER BY date::date DESC))) AS time
                FROM reading 
                GROUP BY date::date 
                ORDER BY date::date DESC 
                limit :limit',
            ['limit' => $limit]
        )->fetchAllAssociative();
    }
}
