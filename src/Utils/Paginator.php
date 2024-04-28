<?php
declare(strict_types=1);

namespace App\Utils;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator as ORMPaginator;

class Paginator
{
    private int $total;
    private int $lastPage;
    private int $currentPage;
    private int $limit;

    private ORMPaginator $items;

    public function paginate(QueryBuilder|Query $query, int $page = 1, int $limit = 21): Paginator
    {
        $this->currentPage = max(1, $page);
        $this->limit = max(1, $limit - 1);
        $paginator = new ORMPaginator($query);

        $paginator
            ->getQuery()
            ->setFirstResult(max(0,$limit * ($page - 1) - 1))
            ->setMaxResults($limit);

        $this->total = $paginator->count();
        $this->lastPage = (int) ceil($paginator->count() / $paginator->getQuery()->getMaxResults());
        $this->items = $paginator;

        return $this;
    }

    public function getCurrentPage(): int
    {
        return $this->currentPage;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getLastPage(): int
    {
        return $this->lastPage;
    }

    public function getItems(): ORMPaginator
    {
        return $this->items;
    }
}
