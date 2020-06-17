<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Account;
use App\Entity\Company;
use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class AccountVoter extends Voter
{
    public const PRE_CREATE = 'pre_create_account';
    public const CREATE = 'create';
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function supports(string $attribute, $subject): bool
    {
        $attributes = [static::CREATE, static::VIEW, static::EDIT, static::DELETE];

        return (\in_array($attribute, $attributes, true) && $subject instanceof Account)
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

        /** @var Account $account */
        $account = $subject;

        $isUserInAccountCompany = $isUserAdminOfAccountCompany = false;
        if ($company = $account->getCompany()) {
            $isUserInAccountCompany = null !== $company->getRightOf($user);
            $isUserAdminOfAccountCompany = $company->isUserAdmin($user);
        }

        switch ($attribute) {
            case self::VIEW:
                return
                    // Company admin can see all accounts into company
                    $isUserInAccountCompany;

            case self::CREATE:
            case self::EDIT:
                return
                    // User should be admin of the company to edit account.
                    $isUserAdminOfAccountCompany;

            case self::DELETE:
                return
                    // You should remove all transactions from account before delete it
                    !$account->getTransactions()->count()
                    // User should be admin of the company to delete account.
                    && $isUserAdminOfAccountCompany;
        }

        throw new LogicException('This code should not be reached!');
    }
}
