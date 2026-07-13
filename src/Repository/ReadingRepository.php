<?php

namespace App\Repository;

use App\Entity\Reading;
use App\Model\ReadingDate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Exception;
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

    /**
     * @return ReadingDate[]
     *
     * @throws Exception
     */
    public function findLatestRecords(int $limit, int $page = 1): array
    {
        $connection = $this->getEntityManager()->getConnection();

        $result = $connection->executeQuery(
            'SELECT
                    (reading.date::date) AS date,
                    MAX(reading.date) AS full_date,
                    MAX(reading.value) AS value,
                    COALESCE(MAX(reading.value) -  LEAD(MAX(reading.value)) OVER (PARTITION BY reading.device_id ORDER BY reading.date::date DESC), 0) AS usage,
                    COALESCE(EXTRACT(EPOCH FROM (MAX(reading.date) -  LEAD(MAX(reading.date)) OVER (PARTITION BY reading.device_id ORDER BY reading.date::date DESC))), 0) AS time,
                    reading.device_id
                FROM reading
                GROUP BY reading.device_id, reading.date::date 
                ORDER BY reading.date::date DESC
                offset :offset
                limit :limit',
            ['limit' => $limit, 'offset' => ($page * $limit) - $limit]
        );

        return array_map(function (array $row) {
            return new ReadingDate(...$row);
        }, $result->fetchAllAssociative());
    }

    /**
     * @throws Exception
     */
    public function recordSummaryCount(): int
    {
        $connection = $this->getEntityManager()->getConnection();

        return $connection->executeQuery(
            'SELECT count(*) FROM (SELECT
                    (reading.date::date) AS date
                FROM reading
                GROUP BY reading.device_id, reading.date::date 
                ) as recrods',
        )->fetchNumeric()[0];
    }
}
