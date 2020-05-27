<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFixtures extends Fixture
{
    public const EMAIL = 'user@doctrine.fixture';
    public const PASSWORD = 'password';

    private UserPasswordEncoderInterface $passwordEncoder;

    /**
     * UserFixtures constructor.
     *
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $passwordEncoder
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager): void
    {
        $myUser = (new User())->setEmail(static::EMAIL);

        $myUser->setPassword($this->passwordEncoder->encodePassword($myUser, static::PASSWORD));

        $manager->persist($myUser);
        $manager->flush();
    }
}
