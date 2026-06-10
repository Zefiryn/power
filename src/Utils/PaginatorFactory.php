<?php
declare(strict_types=1);

namespace App\Utils;

class PaginatorFactory
{
    public function createPaginator(): Paginator
    {
        return new Paginator();
    }
}
