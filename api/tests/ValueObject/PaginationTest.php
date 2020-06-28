<?php

declare(strict_types=1);

namespace App\Tests\ValueObject;

use App\ValueObject\Pagination;
use PHPUnit\Framework\TestCase;

class PaginationTest extends TestCase
{
    /**
     * @covers \App\ValueObject\Pagination::__construct
     * @covers \App\ValueObject\Pagination::hasNext
     */
    public function testHasNext(): void
    {
        $pagination = new Pagination($page = 3, $total = 150);
        $this->assertTrue($pagination->hasNext());
        $pagination = new Pagination($page = 5, $total = 150);
        $this->assertFalse($pagination->hasNext());
    }

    /**
     * @covers \App\ValueObject\Pagination::__construct
     * @covers \App\ValueObject\Pagination::hasPrev
     */
    public function testHasPrev(): void
    {
        $pagination = new Pagination($page = 3, $total = 150);
        $this->assertTrue($pagination->hasPrev());
        $pagination = new Pagination($page = 1, $total = 150);
        $this->assertFalse($pagination->hasPrev());
    }

    /**
     * @covers \App\ValueObject\Pagination::__construct
     * @covers \App\ValueObject\Pagination::getPage
     */
    public function testGetPage(): void
    {
        $pagination = new Pagination($page = 3, $total = 150);
        $this->assertEquals($page, $pagination->getPage());
    }

    /**
     * @covers \App\ValueObject\Pagination::__construct
     * @covers \App\ValueObject\Pagination::getLimit
     */
    public function testGetLimit(): void
    {
        $pagination = new Pagination($page = 3, $total = 150);
        $this->assertEquals(30, $pagination->getLimit());
    }

    /**
     * @covers \App\ValueObject\Pagination::__construct
     * @covers \App\ValueObject\Pagination::getOffset
     */
    public function testGetOffset(): void
    {
        $pagination = new Pagination($page = 3, $total = 150);
        $this->assertEquals((30 * ($page - 1)), $pagination->getOffset());
    }

    /**
     * @covers \App\ValueObject\Pagination::__construct
     * @covers \App\ValueObject\Pagination::getPages
     */
    public function testGetPages(): void
    {
        $pagination = new Pagination($page = 3, $total = 150);
        $this->assertEquals($total / 30, $pagination->getPages());
    }

    /**
     * @covers \App\ValueObject\Pagination::__construct
     * @covers \App\ValueObject\Pagination::getClosest
     */
    public function testGetClosest(): void
    {
        $pagination = new Pagination($page = 3, $total = 150);
        $this->assertEquals([1, 2, 3, 4, 5], $pagination->getClosest(2));
    }
}
