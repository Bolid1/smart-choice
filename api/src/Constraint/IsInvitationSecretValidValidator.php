<?php

declare(strict_types=1);

namespace App\Constraint;

use App\Entity\Invitation;
use App\Security\InvitationSecretEncoder;
use Symfony\Component\Form\Form;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class IsInvitationSecretValidValidator extends ConstraintValidator
{
    private InvitationSecretEncoder $encoder;

    /**
     * IsInvitationSecretValidValidator constructor.
     *
     * @param \App\Security\InvitationSecretEncoder $encoder
     */
    public function __construct(InvitationSecretEncoder $encoder)
    {
        $this->encoder = $encoder;
    }

    public function validate($value, Constraint $constraint): void
    {
        if (!$constraint instanceof IsInvitationSecretValid) {
            throw new UnexpectedTypeException($constraint, IsInvitationSecretValid::class);
        }

        $invitation = $this->context->getObject();
        if ($invitation instanceof Form) {
            $invitation = $invitation->getParent() ?: $invitation;
            $invitation = $invitation->getData();
        }

        if (!$invitation instanceof Invitation) {
            throw new UnexpectedTypeException($invitation, Invitation::class);
        }

        if (!$value || !$this->encoder->isSecretValid($invitation, $value)) {
            $this->context
                ->buildViolation('This value is not valid.')
                ->addViolation();
        }
    }
}
