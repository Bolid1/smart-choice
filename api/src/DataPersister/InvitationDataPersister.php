<?php

declare(strict_types=1);

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\Invitation;
use App\Security\InvitationSecretEncoder;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Overrides default behavior of user changing.
 */
class InvitationDataPersister implements DataPersisterInterface
{
    private EntityManagerInterface $manager;
    private InvitationSecretEncoder $encoder;

    /**
     * InvitationDataPersister constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $manager
     * @param \App\Security\InvitationSecretEncoder $encoder
     */
    public function __construct(EntityManagerInterface $manager, InvitationSecretEncoder $encoder)
    {
        $this->manager = $manager;
        $this->encoder = $encoder;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data): bool
    {
        return $data instanceof Invitation;
    }

    /**
     * If user creates invitation, he should has access to invitation as admin.
     *
     * @param Invitation $invitation
     *
     * @return Invitation
     */
    public function persist($invitation): Invitation
    {
        if ($secret = $invitation->getPlainSecret()) {
            $invitation->setSecret($this->encoder->encodeSecret($invitation, $secret));
            $invitation->erasePlainSecret();
        }

        if (!$this->manager->contains($invitation)) {
            $this->manager->persist($invitation);
        }

        $this->manager->flush();
        $this->manager->refresh($invitation);

        return $invitation;
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
