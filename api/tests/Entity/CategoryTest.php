<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Category;
use PHPUnit\Framework\TestCase;

class CategoryTest extends TestCase
{
    /**
     * @covers \App\Entity\Category::__toString
     */
    public function testToString(): void
    {
        $category = new Category();
        $category->name = 'My category';
        $this->assertSame($category->name, (string)$category);
    }
}
