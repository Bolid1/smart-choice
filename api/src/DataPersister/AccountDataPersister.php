<?php

declare(strict_types=1);

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\Account;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Overrides default behavior of user changing.
 */
class AccountDataPersister implements DataPersisterInterface
{
    private EntityManagerInterface $manager;

    /**
     * AccountDataPersister constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $manager
     */
    public function __construct(EntityManagerInterface $manager)
    {
        $this->manager = $manager;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data): bool
    {
        return $data instanceof Account;
    }

    /**
     * If user creates account, he should has access to account as admin.
     *
     * @param Account $account
     *
     * @return Account
     */
    public function persist($account): Account
    {
        if (!$this->manager->contains($account)) {
            $this->manager->persist($account);
        }

        $this->manager->flush();
        $this->manager->refresh($account);

        return $account;
    }

    /**
     * {@inheritdoc}
     */
    public function remove($data): void
    {
        $this->manager->remove($data);
        $this->manager->flush();
    }
}
