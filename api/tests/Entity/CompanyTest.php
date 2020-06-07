<?php

namespace App\Tests\Entity;

use App\Entity\Company;
use App\Entity\Right;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class CompanyTest extends TestCase
{
    /**
     * @covers \App\Entity\Company::getId
     */
    public function testId(): void
    {
        $this->assertNull($this->createCompany()->getId());
    }

    /**
     * @covers \App\Entity\Company::setName
     * @covers \App\Entity\Company::getName
     */
    public function testName(): void
    {
        $company = $this->createCompany();
        $company->setName($name = 'My company');
        $this->assertEquals($name, $company->getName());
    }

    /**
     * @covers \App\Entity\Company::__construct()
     * @covers \App\Entity\Company::getRights()
     * @covers \App\Entity\Company::addRight()
     * @covers \App\Entity\Company::removeRight()
     */
    public function testRights(): void
    {
        $company = $this->createCompany();
        $this->assertInstanceOf(Collection::class, $company->getRights());
        $company->addRight($right = new Right());
        $this->assertSame($right, $company->getRights()->first());
        $company->removeRight($right);
        $this->assertFalse($company->getRights()->first());
    }

    /**
     * @covers \App\Entity\Company::__toString()
     */
    public function testToString(): void
    {
        $company = ($this->createCompany())->setName($name = 'My company');
        $this->assertSame($name, (string)$company);
    }

    private function createCompany(): Company
    {
        return new Company();
    }
}
