<?php

declare(strict_types=1);

namespace App\Controller\API\V0;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use App\Entity\Invitation;
use App\Entity\User;
use App\Service\InvitationAcceptor;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @IsGranted("accept", subject="data")
 */
class AcceptInvitationAction
{
    private InvitationAcceptor $acceptor;
    private Security $security;
    private ValidatorInterface $validator;

    /**
     * AcceptInvitationAction constructor.
     *
     * @param InvitationAcceptor $acceptor
     * @param ValidatorInterface $validator
     * @param Security $security
     */
    public function __construct(InvitationAcceptor $acceptor, ValidatorInterface $validator, Security $security)
    {
        $this->acceptor = $acceptor;
        $this->security = $security;
        $this->validator = $validator;
    }

    public function __invoke(Invitation $data, Request $request)
    {
        $plainSecret = $request->getContent();
        if ($plainSecret && \is_string($plainSecret)) {
            $data->setPlainSecret($plainSecret);
        }

        $violations = $this->validator->validate($data, null, ['Default', 'invitation:accept']);
        if (0 !== \count($violations)) {
            throw new ValidationException($violations);
        }

        $user = $this->security->getUser();

        if ($user instanceof User) {
            $this->acceptor->accept($data, $user);
        }

        return $data;
    }
}
