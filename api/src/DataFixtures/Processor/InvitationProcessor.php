<?php

declare(strict_types=1);

namespace App\DataFixtures\Processor;

use App\Entity\Invitation;
use App\Security\InvitationSecretEncoder;
use Fidry\AliceDataFixtures\ProcessorInterface;

class InvitationProcessor implements ProcessorInterface
{
    private InvitationSecretEncoder $invitationSecretEncoder;

    /**
     * InvitationProcessor constructor.
     *
     * @param InvitationSecretEncoder $invitationSecretEncoder
     */
    public function __construct(InvitationSecretEncoder $invitationSecretEncoder)
    {
        $this->invitationSecretEncoder = $invitationSecretEncoder;
    }

    /**
     * Encode plainSecret to secret before insert invitation in database.
     *
     * @param string $id
     * @param object $object
     */
    public function preProcess(string $id, $object): void
    {
        if ($object instanceof Invitation && $object->getPlainSecret()) {
            $object->setSecret(
                $this->invitationSecretEncoder->encodeSecret(
                    $object,
                    $object->getPlainSecret()
                )
            );
        }
    }

    public function postProcess(string $id, $object): void
    {
        // Nothing to do, yet
    }
}
