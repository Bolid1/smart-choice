<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Company;
use App\Entity\Right;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    /**
     * @covers \App\Entity\User::getId
     */
    public function testId(): void
    {
        $this->assertNull($this->createUser()->getId());
    }

    /**
     * @covers \App\Entity\User::setPlainPassword
     * @covers \App\Entity\User::getPlainPassword
     * @covers \App\Entity\User::eraseCredentials
     */
    public function testPlainPassword(): void
    {
        $user = $this->createUser();
        $user->setPlainPassword($password = 'foo');
        $this->assertEquals($password, $user->getPlainPassword());
        $user->eraseCredentials();
        $this->assertNull($user->getPlainPassword());
    }

    /**
     * @covers \App\Entity\User::setEmail
     * @covers \App\Entity\User::getEmail
     * @covers \App\Entity\User::getUsername
     */
    public function testEmail(): void
    {
        $user = $this->createUser();
        $user->setEmail($email = 'foo@bar.baz');
        $this->assertEquals($email, $user->getEmail());
        $this->assertEquals($email, $user->getUsername());
    }

    /**
     * @covers \App\Entity\User::setPassword
     * @covers \App\Entity\User::getPassword
     */
    public function testPassword(): void
    {
        $user = $this->createUser();
        $user->setPassword($password = 'foo');
        $this->assertEquals($password, $user->getPassword());
    }

    /**
     * @covers \App\Entity\User::getSalt
     */
    public function testSalt(): void
    {
        $user = $this->createUser();
        $this->assertNull($user->getSalt());
    }

    /**
     * @covers \App\Entity\User::getRoles
     */
    public function testRoles(): void
    {
        $user = $this->createUser();
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    /**
     * @covers \App\Entity\User::getCreatedAt
     */
    public function testCreatedAt(): void
    {
        $user = $this->createUser();
        $this->assertNull($user->getCreatedAt());
    }

    /**
     * @covers \App\Entity\User::getUpdatedAt
     */
    public function testUpdatedAt(): void
    {
        $user = $this->createUser();
        $this->assertNull($user->getUpdatedAt());
    }

    /**
     * @covers \App\Entity\User::__construct()
     * @covers \App\Entity\User::getRights()
     * @covers \App\Entity\User::addRight()
     * @covers \App\Entity\User::removeRight()
     */
    public function testRights(): void
    {
        $user = $this->createUser();
        $this->assertInstanceOf(Collection::class, $user->getRights());
        $user->addRight($right = new Right());
        $this->assertSame($right, $user->getRights()->first());
        $user->removeRight($right);
        $this->assertFalse($user->getRights()->first());
    }

    /**
     * @covers \App\Entity\User::getCompanies()
     */
    public function testGetCompanies(): void
    {
        $user = $this->createUser();
        $this->assertInstanceOf(Collection::class, $user->getRights());
        $user->addRight((new Right())->setCompany($company = new Company()));

        $this->assertSame($company, $user->getCompanies()->first());
    }

    /**
     * @covers \App\Entity\User::isLimitForCompaniesReached()
     */
    public function testLimitForCompanies(): void
    {
        $user = $this->createUser();
        $this->assertInstanceOf(Collection::class, $user->getRights());

        for ($i = 0; $i < Right::MAX_FOR_USER; ++$i) {
            $this->assertFalse($user->isLimitForCompaniesReached());
            $user->addRight(new Right());
        }

        $this->assertTrue($user->isLimitForCompaniesReached());
    }

    /**
     * @covers \App\Entity\User::__toString()
     */
    public function testToString(): void
    {
        $user = ($this->createUser())->setEmail($email = 'foo@bar.baz');
        $this->assertSame($email, (string)$user);
    }

    private function createUser(): User
    {
        return new User();
    }
}
