<?php

declare(strict_types=1);

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\Transaction;
use Doctrine\ORM\EntityManagerInterface;

class TransactionDataPersister implements DataPersisterInterface
{
    private EntityManagerInterface $manager;

    /**
     * TransactionDataPersister constructor.
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
        return $data instanceof Transaction;
    }

    /**
     * If user creates transaction, he should has access to transaction as admin.
     *
     * @param Transaction $transaction
     *
     * @return Transaction
     */
    public function persist($transaction): Transaction
    {
        if (!$this->manager->contains($transaction)) {
            $this->manager->persist($transaction);
        }

        $this->manager->flush();
        $this->manager->refresh($transaction);

        return $transaction;
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
