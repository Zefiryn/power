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

    /** @var ORMPaginator<mixed> */
    private ORMPaginator $items;

    public function paginate(QueryBuilder|Query $query, int $page = 1, int $limit = 20, bool $hideLast = false): Paginator
    {
        $hideNumber = $hideLast ? 1 : 0;
        $this->currentPage = max(1, $page);
        $this->limit = max(1, $limit - $hideNumber);
        $paginator = new ORMPaginator($query);

        $paginator
            ->getQuery()
            ->setFirstResult(max(0, $limit * ($page - 1) - $hideNumber))
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

    /** @return ORMPaginator<mixed> */
    public function getItems(): ORMPaginator
    {
        return $this->items;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }
}
