<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Company;
use App\Entity\Right;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class RightTest extends TestCase
{
    private User $user;
    private Company $company;
    private Right $right;

    protected function setUp(): void
    {
        $this->user = new User();
        $this->company = new Company();
        $this->right = new Right();
    }

    /**
     * @covers \App\Entity\Right::setUser()
     * @covers \App\Entity\Right::getUser()
     */
    public function testUser(): void
    {
        $this->assertNull($this->right->getUser());
        $this->right->setUser($this->user);
        $this->assertSame($this->user, $this->right->getUser());
    }

    /**
     * @covers \App\Entity\Right::setCompany()
     * @covers \App\Entity\Right::getCompany()
     */
    public function testCompany(): void
    {
        $this->assertNull($this->right->getCompany());
        $this->right->setCompany($this->company);
        $this->assertSame($this->company, $this->right->getCompany());
    }

    /**
     * @covers \App\Entity\Right::isUserCompanyAdmin()
     */
    public function testIsUserCompanyAdmin(): void
    {
        $this->assertFalse($this->right->isUserCompanyAdmin($this->user));
        $this->company->addRight($this->right);
        $this->user->addRight($this->right);
        $this->right->setAdmin(true);

        $this->assertTrue($this->right->isUserCompanyAdmin($this->user));
    }

    /**
     * @covers \App\Entity\Right::isAdmin()
     * @covers \App\Entity\Right::setAdmin()
     */
    public function testAdmin(): void
    {
        $this->assertFalse($this->right->isAdmin());
        $this->right->setAdmin(true);
        $this->assertTrue($this->right->isAdmin());
    }
}
