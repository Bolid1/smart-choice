<?php

declare(strict_types=1);

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\DataPersisterInterface;
use App\Entity\ImportTransactionsTask;
use App\Service\TaskStarter;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Overrides default behavior of user changing.
 */
class ImportTransactionsTaskDataPersister implements DataPersisterInterface
{
    private EntityManagerInterface $manager;
    private TaskStarter $starter;

    /**
     * ImportTransactionsTaskDataPersister constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $manager
     * @param \App\Service\TaskStarter $starter
     */
    public function __construct(EntityManagerInterface $manager, TaskStarter $starter)
    {
        $this->manager = $manager;
        $this->starter = $starter;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($data): bool
    {
        return $data instanceof ImportTransactionsTask;
    }

    /**
     * @param ImportTransactionsTask $task
     *
     * @return ImportTransactionsTask
     */
    public function persist($task): ImportTransactionsTask
    {
        if (!$this->manager->contains($task)) {
            $this->manager->persist($task);
        }

        $this->starter->start($task);
        $this->manager->refresh($task);

        return $task;
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
