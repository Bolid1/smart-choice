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
    public const ACCEPT = 'accept';
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
        $attributes = [static::CREATE, static::VIEW, static::EDIT, static::DELETE, static::ACCEPT];

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

        $isUserAdminOfInvitationCompany = ($company = $invitation->getToCompany()) && $company->isUserAdmin($user);

        switch ($attribute) {
            case self::VIEW:
                return
                    // Company admin can see all invitations into company
                    $isUserAdminOfInvitationCompany;

            case self::CREATE:
                $invitedUser = ($email = $invitation->getEmail())
                    ? $this->userRepository->findOneByEmail($email)
                    : null;
                $isInvitedUserAlreadyInCompany = $company && $invitedUser && $company->getRightOf($invitedUser);

                return
                    // User should be the company admin to invite someone
                    $isUserAdminOfInvitationCompany
                    // Check if invited user already in company
                    && !$isInvitedUserAlreadyInCompany;

            case self::EDIT:
                return
                    // User should be the company admin to invite someone
                    $isUserAdminOfInvitationCompany;

            case self::DELETE:
                return
                    // User should be the company admin to delete invitation
                    $isUserAdminOfInvitationCompany
                    && $invitation->getId();

            case self::ACCEPT:
                $invitedUser = ($email = $invitation->getEmail())
                    ? $this->userRepository->findOneByEmail($email)
                    : null;

                return
                    // Check if invited current user invited
                    $invitedUser === $user;
        }

        throw new LogicException('This code should not be reached!');
    }
}
