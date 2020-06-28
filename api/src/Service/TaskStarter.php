<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\ImportTransactionsTask;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

class TaskStarter
{
    private EntityManagerInterface $manager;
    private MessageBusInterface $bus;

    /**
     * TaskStarter constructor.
     *
     * @param \Doctrine\ORM\EntityManagerInterface $manager
     * @param \Symfony\Component\Messenger\MessageBusInterface $bus
     */
    public function __construct(EntityManagerInterface $manager, MessageBusInterface $bus)
    {
        $this->manager = $manager;
        $this->bus = $bus;
    }

    public function start(ImportTransactionsTask $task): void
    {
        $task->beforeSchedule();
        $this->manager->flush();
        $this->bus->dispatch(new \App\Message\ImportTransactionsTask($task->getId()));
    }
}
