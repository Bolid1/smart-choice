<?php

declare(strict_types=1);

namespace App\Controller\API\V0;

use App\Entity\Invitation;
use App\Entity\User;
use App\Service\InvitationAcceptor;
use Symfony\Component\Security\Core\Security;

class AcceptInvitation
{
    private InvitationAcceptor $acceptor;
    private Security $security;

    /**
     * AcceptInvitation constructor.
     *
     * @param \App\Service\InvitationAcceptor $acceptor
     * @param \Symfony\Component\Security\Core\Security $security
     */
    public function __construct(InvitationAcceptor $acceptor, Security $security)
    {
        $this->acceptor = $acceptor;
        $this->security = $security;
    }

    public function __invoke(Invitation $data)
    {
        $user = $this->security->getUser();

        if ($user instanceof User) {
            $this->acceptor->accept($data, $user);
        }

        return $data;
    }
}
