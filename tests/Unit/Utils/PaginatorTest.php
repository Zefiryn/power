<?php

declare(strict_types=1);

namespace App\Tests\Unit\Utils;

use App\Tests\Fixtures\Entity\TestPaginatorItem;
use App\Utils\Paginator;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\ORMSetup;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class PaginatorTest extends KernelTestCase
{
    private EntityManager $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();

        $config = ORMSetup::createAttributeMetadataConfiguration(
            paths: [self::getContainer()->getParameter('kernel.project_dir').'/tests/Fixtures/Entity'],
            isDevMode: true,
        );

        $connection = DriverManager::getConnection([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ], $config);

        $this->entityManager = new EntityManager($connection, $config);

        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = [$this->entityManager->getClassMetadata(TestPaginatorItem::class)];
        $schemaTool->createSchema($metadata);

        for ($i = 1; $i <= 25; ++$i) {
            $item = new TestPaginatorItem();
            $item->setName('Item '.$i);
            $this->entityManager->persist($item);
        }

        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        $this->entityManager->close();
        parent::tearDown();
    }

    public function testPaginateWithDefaultBehavior(): void
    {
        $queryBuilder = $this->createQueryBuilder();
        $paginator = (new Paginator())->paginate($queryBuilder);

        self::assertSame(1, $paginator->getCurrentPage());
        self::assertSame(20, $paginator->getLimit());
        self::assertSame(25, $paginator->getTotal());
        self::assertSame(2, $paginator->getLastPage());
        self::assertSame(20, iterator_count($paginator->getItems()->getIterator()));
    }

    public function testPaginateWithHideLastEnabled(): void
    {
        $queryBuilder = $this->createQueryBuilder();
        $paginator = (new Paginator())->paginate($queryBuilder, page: 2, limit: 10, hideLast: true);

        self::assertSame(2, $paginator->getCurrentPage());
        self::assertSame(9, $paginator->getLimit());
        self::assertSame(25, $paginator->getTotal());
        self::assertSame(3, $paginator->getLastPage());
        self::assertSame(10, iterator_count($paginator->getItems()->getIterator()));
    }

    public function testPaginateWithZeroLimitThrowsDivisionByZeroError(): void
    {
        $this->expectException(\DivisionByZeroError::class);

        $queryBuilder = $this->createQueryBuilder();
        (new Paginator())->paginate($queryBuilder, page: 0, limit: 0);
    }

    private function createQueryBuilder(): QueryBuilder
    {
        return $this->entityManager
            ->getRepository(TestPaginatorItem::class)
            ->createQueryBuilder('i')
            ->orderBy('i.id', 'ASC');
    }
}
