<?php

declare(strict_types=1);

namespace App\Security\Voter;

use App\Entity\Company;
use App\Entity\TransactionCategory;
use App\Entity\User;
use App\Security\TransactionVoter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class TransactionCategoryVoter extends Voter
{
    public const PRE_CREATE = 'pre_create_transaction_category';
    public const CREATE = 'create';
    public const VIEW = 'view';
    public const EDIT = 'edit';
    public const DELETE = 'delete';

    private TransactionVoter $voter;

    /**
     * TransactionCategoryVoter constructor.
     *
     * @param \App\Security\TransactionVoter $voter
     */
    public function __construct(TransactionVoter $voter)
    {
        $this->voter = $voter;
    }

    protected function supports(string $attribute, $subject): bool
    {
        $attributes = [static::CREATE, static::VIEW, static::EDIT, static::DELETE];

        return (\in_array($attribute, $attributes, true) && $subject instanceof TransactionCategory)
               || (static::PRE_CREATE === $attribute && $subject instanceof Company);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            // the user must be logged in; if not, deny access
            return false;
        }

        if ((static::PRE_CREATE === $attribute) && ($subject instanceof Company)) {
            // If you has rights to company, you can manage it's transactions property.
            return null !== $subject->getRightOf($user);
        }

        $transaction = ($subject instanceof TransactionCategory) ? $subject->getTransaction() : null;

        if (null === $transaction) {
            // You can manage transaction-category links only when transaction exist.
            return false;
        }

        // If you can edit transaction, you can manage it links with categories.
        return self::ACCESS_GRANTED === $this->voter->vote($token, $transaction, [TransactionVoter::EDIT]);
    }
}
