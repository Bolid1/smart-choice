<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Right;
use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class RightVoter extends Voter
{
    public const PRE_CREATE = 'pre_create_right';
    public const CREATE = 'create';
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function supports(string $attribute, $subject): bool
    {
        $attributes = [static::CREATE, static::VIEW, static::EDIT, static::DELETE];

        return
            static::PRE_CREATE === $attribute
            || (\in_array($attribute, $attributes, true) && $subject instanceof Right);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        /** @var Right $right */
        $right = $subject;

        switch ($attribute) {
            case self::VIEW:
                return
                    // User can know his rights
                    ($right->getUser() === $user)
                    // Company admin can see rights of all users in company
                    || $right->isUserCompanyAdmin($user);
            case self::PRE_CREATE:
                return
                    // Users has quota for memberships in companies.
                    !$user->isLimitForCompaniesReached();
            case self::CREATE:
                return
                    // Companies has quota for users count.
                    (($company = $right->getCompany()) && !$company->isLimitForUsersReached())
                    // User can't add himself to company
                    && ($right->getUser() !== $user)
                    // User should be the company admin to edit rights
                    && $right->isUserCompanyAdmin($user);
            case self::EDIT:
                return
                    // User can't edit his rights for company
                    ($right->getUser() !== $user)
                    // User should be the company admin to edit rights
                    && $right->isUserCompanyAdmin($user);
            case self::DELETE:
                return
                    // User can't delete his rights for company
                    ($right->getUser() !== $user)
                    // User can't delete rights of another admin
                    && !$right->isAdmin()
                    // User should be the company admin to delete rights
                    && $right->isUserCompanyAdmin($user);
        }

        throw new LogicException('This code should not be reached!');
    }
}
