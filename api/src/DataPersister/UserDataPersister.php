<?php

declare(strict_types=1);

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Overrides default behavior of user changing.
 */
class UserDataPersister implements DataPersisterInterface
{
    private EntityManagerInterface $manager;
    private UserPasswordEncoderInterface $userPasswordEncoder;

    /**
     * UserDataPersister constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $manager
     * @param \Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface $userPasswordEncoder
     */
    public function __construct(EntityManagerInterface $manager, UserPasswordEncoderInterface $userPasswordEncoder)
    {
        $this->manager = $manager;
        $this->userPasswordEncoder = $userPasswordEncoder;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data): bool
    {
        return $data instanceof User;
    }

    /**
     * We should encode plain password before save user.
     *
     * @param User $user
     *
     * @return User
     */
    public function persist($user): User
    {
        if ($user->getPlainPassword()) {
            $user->setPassword(
                $this->userPasswordEncoder->encodePassword($user, $user->getPlainPassword())
            );
            $user->eraseCredentials();
        }

        if (!$this->manager->contains($user)) {
            $this->manager->persist($user);
        }

        $this->manager->flush();
        $this->manager->refresh($user);

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($data): void
    {
        $this->manager->remove($data);
        $this->manager->flush();
    }
}
