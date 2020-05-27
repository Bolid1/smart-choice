<?php

declare(strict_types=1);

namespace App\DataFixtures\Processor;

use App\Entity\User;
use Fidry\AliceDataFixtures\ProcessorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserProcessor implements ProcessorInterface
{
    private UserPasswordEncoderInterface $userPasswordEncoder;

    /**
     * UserProcessor constructor.
     *
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $userPasswordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    /**
     * Encode plainPassword to password before insert user in database.
     *
     * @param string $id
     * @param object $object
     */
    public function preProcess(string $id, $object): void
    {
        if ($object instanceof User && $object->getPlainPassword()) {
            $object->setPassword(
                $this->userPasswordEncoder->encodePassword(
                    $object,
                    $object->getPlainPassword()
                )
            );
            $object->eraseCredentials();
        }
    }

    public function postProcess(string $id, $object): void
    {
        // Nothing to do, yet
    }
}
