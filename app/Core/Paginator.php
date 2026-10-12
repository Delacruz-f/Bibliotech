<?php
declare(strict_types=1);

namespace App\Core;

final class Paginator
{
    public readonly int $page;
    public readonly int $pages;
    public readonly int $offset;

    public function __construct(public readonly int $total, public readonly int $perPage = 10, mixed $requestedPage = 1)
    {
        $this->pages = max(1, (int) ceil($total / $perPage));
        $this->page = min(max(1, (int) $requestedPage), $this->pages);
        $this->offset = ($this->page - 1) * $perPage;
    }
}
