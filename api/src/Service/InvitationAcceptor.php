<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Invitation;
use App\Entity\Right;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class InvitationAcceptor
{
    private EntityManagerInterface $manager;

    /**
     * InvitationAcceptor constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    public function accept(Invitation $invitation, User $user): void
    {

        $right = new Right();
        $right
            ->setUser($user)
            ->setCompany($invitation->getToCompany())
            ->setAdmin($invitation->isAdmin())
        ;

        $this->manager->persist($right);
        $this->manager->remove($invitation);
        $this->manager->flush();
    }
}
