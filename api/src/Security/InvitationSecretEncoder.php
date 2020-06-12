<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Invitation;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class InvitationSecretEncoder
{
    private EncoderFactoryInterface $encoderFactory;

    public function __construct(EncoderFactoryInterface $encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
    }

    public function encodeSecret(Invitation $invitation, string $plainSecret): string
    {
        $encoder = $this->encoderFactory->getEncoder($invitation);

        return $encoder->encodePassword($plainSecret, null);
    }

    public function isSecretValid(Invitation $invitation, string $raw): bool
    {
        if (null === $secret = $invitation->getSecret()) {
            return false;
        }

        $encoder = $this->encoderFactory->getEncoder($invitation);

        return $encoder->isPasswordValid($secret, $raw, null);
    }

    public function needsRehash(Invitation $invitation): bool
    {
        if (null === $secret = $invitation->getSecret()) {
            return false;
        }

        $encoder = $this->encoderFactory->getEncoder($invitation);

        return $encoder->needsRehash($secret);
    }
}
