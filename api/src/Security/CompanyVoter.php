<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Company;
use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CompanyVoter extends Voter
{
    public const PRE_CREATE = 'pre_create_company';
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';
    public const IS_ADMIN = 'is_company_admin';

    protected function supports(string $attribute, $subject): bool
    {
        $attributes = [static::VIEW, static::EDIT, static::DELETE, static::IS_ADMIN];

        return
            static::PRE_CREATE === $attribute
            || (\in_array($attribute, $attributes, true) && $subject instanceof Company);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        /** @var Company $company */
        $company = $subject;

        switch ($attribute) {
            case self::IS_ADMIN:
                return $company->isUserAdmin($user);

            case self::PRE_CREATE:
                return
                    // Users has quota for memberships in companies.
                    !$user->isLimitForCompaniesReached();

            case self::VIEW:
                return
                    // User can see only his companies.
                    null !== $company->getRightOf($user);

            case self::EDIT:
                return
                    // User should be admin of the company to edit it.
                    $company->isUserAdmin($user);

            case self::DELETE:
                return
                    // User should be admin of the company to delete it.
                    $company->isUserAdmin($user)
                    // User should be admin of the company to edit it.
                    && 1 === $company->getRights()->count();
        }

        throw new LogicException('This code should not be reached!');
    }
}
