<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\Invitation;
use App\Security\InvitationSecretEncoder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class InvitationSecretEncoderTest extends TestCase
{
    private InvitationSecretEncoder $encoder;
    /** @var \PHPUnit\Framework\MockObject\MockObject|EncoderFactoryInterface */
    private EncoderFactoryInterface $factory;

    protected function setUp(): void
    {
        $this->encoder = new InvitationSecretEncoder(
            $this->factory = $this->createMock(EncoderFactoryInterface::class),
        );
    }

    /**
     * @covers \App\Security\InvitationSecretEncoder::encodeSecret
     */
    public function testEncodeSecret(): void
    {
        $invitation = new Invitation();

        $this
            ->createEncoder($invitation)
            ->expects($this->once())
            ->method('encodePassword')
            ->with($plain = 'secret', null)
            ->willReturn($expected = 'result')
        ;

        $this->assertSame($expected, $this->encoder->encodeSecret($invitation, $plain));
    }

    /**
     * @covers \App\Security\InvitationSecretEncoder::isSecretValid
     */
    public function testIsSecretValid(): void
    {
        $invitation = new Invitation();
        $raw = 'secret';
        $this->assertFalse($this->encoder->isSecretValid($invitation, $raw));

        $invitation->setSecret($secret = 'encoded secret');

        $this
            ->createEncoder($invitation)
            ->expects($this->once())
            ->method('isPasswordValid')
            ->with($secret, $raw, null)
            ->willReturn(true)
        ;
        $this->assertTrue($this->encoder->isSecretValid($invitation, $raw));
    }

    /**
     * @covers \App\Security\InvitationSecretEncoder::needsRehash
     */
    public function testNeedsRehash(): void
    {
        $invitation = new Invitation();
        $this->assertFalse($this->encoder->needsRehash($invitation));

        $invitation->setSecret($secret = 'encoded secret');

        $this
            ->createEncoder($invitation)
            ->expects($this->once())
            ->method('needsRehash')
            ->with($secret)
            ->willReturn(true)
        ;
        $this->assertTrue($this->encoder->needsRehash($invitation));
    }

    /**
     * @param \App\Entity\Invitation $invitation
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface
     */
    private function createEncoder(Invitation $invitation)
    {
        $this->factory
            ->expects($this->once())
            ->method('getEncoder')
            ->with($invitation)
            ->willReturn($encoder = $this->createMock(PasswordEncoderInterface::class))
        ;

        return $encoder;
    }
}
