<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\User;
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

    private function createUser(): User
    {
        return new User();
    }
}
