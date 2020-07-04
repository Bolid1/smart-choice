<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Category;
use App\Entity\Company;
use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CategoryVoter extends Voter
{
    public const PRE_CREATE = 'pre_create_category';
    public const CREATE = 'create';
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function supports(string $attribute, $subject): bool
    {
        $attributes = [static::CREATE, static::VIEW, static::EDIT, static::DELETE];

        return (\in_array($attribute, $attributes, true) && $subject instanceof Category)
               || (static::PRE_CREATE === $attribute && $subject instanceof Company);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        if (static::PRE_CREATE === $attribute && $subject instanceof Company) {
            return $subject->isUserAdmin($user);
        }

        /** @var Category $category */
        $category = $subject;

        $isUserInCategoryCompany = $isUserAdminOfCategoryCompany = false;
        if ($company = $category->company) {
            $isUserInCategoryCompany = null !== $company->getRightOf($user);
            $isUserAdminOfCategoryCompany = $company->isUserAdmin($user);
        }

        switch ($attribute) {
            case self::VIEW:
                return
                    // Company admin can see all categories into company
                    $isUserInCategoryCompany;

            case self::CREATE:
            case self::EDIT:
            case self::DELETE:
                return
                    // User should be admin of the company to manage category.
                    $isUserAdminOfCategoryCompany;
        }

        throw new LogicException('This code should not be reached!');
    }
}
