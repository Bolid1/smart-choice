<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Transaction;
use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TransactionVoter extends Voter
{
    public const CREATE = 'create';
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function supports(string $attribute, $subject): bool
    {
        $attributes = [static::CREATE, static::VIEW, static::EDIT, static::DELETE];

        return \in_array($attribute, $attributes, true) && $subject instanceof Transaction;
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        /** @var Transaction $transaction */
        $transaction = $subject;
        $account = $transaction->getAccount();
        $company = $account ? $account->getCompany() : null;
        $isUserInCompany = $company && $company->getRightOf($user);

        switch ($attribute) {
            case self::CREATE:
            case self::VIEW:
            case self::EDIT:
            case self::DELETE:
                return
                    // Transfer between companies are forbidden
                    $transaction->getCompany() === $account->getCompany()
                    // User can view, edit and delete transactions of his company
                    && $isUserInCompany;
        }

        throw new LogicException('This code should not be reached!');
    }
}
