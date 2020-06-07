<?php

namespace App\Tests\Entity;

use App\Entity\Company;
use App\Entity\Invitation;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class InvitationTest extends TestCase
{
    private User $user;
    private Company $company;
    private Invitation $invitation;

    protected function setUp(): void
    {
        $this->user = new User();
        $this->company = new Company();
        $this->invitation = new Invitation();
    }

    /**
     * @covers \App\Entity\Invitation::getId
     */
    public function testId(): void
    {
        $this->assertNull($this->invitation->getId());
    }

    /**
     * @covers \App\Entity\Invitation::setPlainSecret
     * @covers \App\Entity\Invitation::getPlainSecret
     */
    public function testPlainSecret(): void
    {
        $invitation = $this->invitation;
        $invitation->setPlainSecret($secret = 'foo');
        $this->assertEquals($secret, $invitation->getPlainSecret());
    }

    /**
     * @covers \App\Entity\Invitation::setEmail
     * @covers \App\Entity\Invitation::getEmail
     */
    public function testEmail(): void
    {
        $invitation = $this->invitation;
        $invitation->setEmail($email = 'foo@bar.baz');
        $this->assertEquals($email, $invitation->getEmail());
    }

    /**
     * @covers \App\Entity\Invitation::setSecret
     * @covers \App\Entity\Invitation::getSecret
     */
    public function testSecret(): void
    {
        $invitation = $this->invitation;
        $invitation->setSecret($secret = 'foo');
        $this->assertEquals($secret, $invitation->getSecret());
    }

    /**
     * @covers \App\Entity\Invitation::getCreatedAt
     */
    public function testCreatedAt(): void
    {
        $invitation = $this->invitation;
        $this->assertNull($invitation->getCreatedAt());
    }

    /**
     * @covers \App\Entity\Invitation::getUpdatedAt
     */
    public function testUpdatedAt(): void
    {
        $invitation = $this->invitation;
        $this->assertNull($invitation->getUpdatedAt());
    }
    /**
     * @covers \App\Entity\Invitation::setFromUser()
     * @covers \App\Entity\Invitation::getFromUser()
     */
    public function testFromUser(): void
    {
        $this->assertNull($this->invitation->getFromUser());
        $this->invitation->setFromUser($this->user);
        $this->assertSame($this->user, $this->invitation->getFromUser());
    }

    /**
     * @covers \App\Entity\Invitation::setToCompany()
     * @covers \App\Entity\Invitation::getToCompany()
     */
    public function testToCompany(): void
    {
        $this->assertNull($this->invitation->getToCompany());
        $this->invitation->setToCompany($this->company);
        $this->assertSame($this->company, $this->invitation->getToCompany());
    }

    /**
     * @covers \App\Entity\Invitation::isAdmin()
     * @covers \App\Entity\Invitation::setAdmin()
     */
    public function testAdmin(): void
    {
        $this->assertFalse($this->invitation->isAdmin());
        $this->invitation->setAdmin(true);
        $this->assertTrue($this->invitation->isAdmin());
    }
}
