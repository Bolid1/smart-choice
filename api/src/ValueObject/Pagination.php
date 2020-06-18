<?php

declare(strict_types=1);

namespace App\ValueObject;

class Pagination
{
    private int $perPage = 30;
    private int $page;
    private int $total;

    /**
     * Pagination constructor.
     *
     * @param int $page
     * @param int $total
     */
    public function __construct(int $page, int $total)
    {
        $this->page = $page;
        $this->total = $total;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function hasPrev(): bool
    {
        return $this->page > 1;
    }

    public function hasNext(): bool
    {
        return $this->page < $this->getPages();
    }

    public function getLimit(): int
    {
        return $this->perPage;
    }

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->perPage;
    }

    public function getPages(): int
    {
        return (int)\ceil($this->total / $this->perPage);
    }

    public function getClosest(int $count): array
    {
        return \range(\max($this->page - $count, 1), \min($this->page + $count, $this->getPages()));
    }
}
