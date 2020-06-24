<?php

declare(strict_types=1);

namespace App\Security;

use App\Entity\Company;
use App\Entity\ImportTransactionsTask;
use App\Entity\User;
use LogicException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ImportTransactionsTaskVoter extends Voter
{
    public const PRE_CREATE = 'pre_create_import_transactions_task';
    public const CREATE = 'create';
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    protected function supports(string $attribute, $subject): bool
    {
        $attributes = [static::CREATE, static::VIEW, static::EDIT, static::DELETE];

        return (\in_array($attribute, $attributes, true) && $subject instanceof ImportTransactionsTask)
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
            return null !== $subject->getRightOf($user);
        }

        /** @var ImportTransactionsTask $task */
        $task = $subject;

        switch ($attribute) {
            case self::CREATE:
            case self::VIEW:
            case self::EDIT:
                return
                    // User can manage imports in his companies
                    null !== $task->company->getRightOf($user);
            case self::DELETE:
                return
                    // User can manage imports in his companies
                    null !== $task->company->getRightOf($user)
                    // User can't cancel task in progress
                    && ImportTransactionsTask::STATUS_STARTED !== $task->status;
        }

        throw new LogicException('This code should not be reached!');
    }
}
