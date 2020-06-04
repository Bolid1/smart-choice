<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Invitation;
use App\Entity\User;
use App\Repository\UserRepository;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class InvitationVoter extends Voter
{
    public const CREATE = 'create';
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    /** @var userRepository */
    private UserRepository $userRepository;

    /**
     * InvitationVoter constructor.
     *
     * @param userRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    protected function supports(string $attribute, $subject): bool
    {
        $attributes = [static::CREATE, static::VIEW, static::EDIT, static::DELETE];

        return \in_array($attribute, $attributes, true) && $subject instanceof Invitation;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        /** @var Invitation $invitation */
        $invitation = $subject;

        switch ($attribute) {
            case self::VIEW:
                return
                    // The user can know who he invited.
                    ($invitation->getFromUser() === $user)
                    // Company admin can see all invitations into company
                    || $this->isUserAdminOfInvitationCompany($invitation, $user);

            case self::CREATE:
                return
                    // User should be the company admin to invite someone
                    ($company = $invitation->getToCompany())
                    && !$company->isLimitForInvitationsReached()
                    && $this->isUserAdminOfInvitationCompany($invitation, $user)
                    // Check if invited user already in company
                    && !$this->isInvitedUserAlreadyInCompany($invitation);

            case self::EDIT:
                return
                    // User should be the company admin to invite someone
                    $this->isUserAdminOfInvitationCompany($invitation, $user)
                    // Check if invited user already in company
                    && !$this->isInvitedUserAlreadyInCompany($invitation);

            case self::DELETE:
                return
                    // User should be the company admin to delete invitation
                    $this->isUserAdminOfInvitationCompany($invitation, $user);
        }

        throw new LogicException('This code should not be reached!');
    }

    private function isUserAdminOfInvitationCompany(Invitation $invitation, User $user): bool
    {
        return ($company = $invitation->getToCompany()) && $company->isUserAdmin($user);
    }

    private function isInvitedUserAlreadyInCompany(Invitation $invitation): bool
    {
        return ($company = $invitation->getToCompany())
            && ($invitedUser = $this->getInvitedUser($invitation))
            && $company->getRightOf($invitedUser);
    }

    private function getInvitedUser(Invitation $invitation)
    {
        return ($email = $invitation->getEmail())
            ? $this->userRepository->findOneByEmail($email)
            : null;
    }
}
