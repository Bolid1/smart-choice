<?php

namespace App\Tests\Service;

use App\Entity\ImportTransactionsTask;
use App\Service\TaskStarter;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class TaskStarterTest extends TestCase
{
    public function testStart(): void
    {
        $starter = new TaskStarter(
            $manager = $this->createMock(EntityManagerInterface::class),
            $bus = $this->createMock(MessageBusInterface::class),
        );
        $task = $this->createMock(ImportTransactionsTask::class);

        $task
            ->expects($this->once())
            ->method('beforeSchedule')
        ;
        $task
            ->expects($this->once())
            ->method('getId')
            ->willReturn($this->createMock(UuidInterface::class))
        ;

        $manager
            ->expects($this->once())
            ->method('flush')
        ;

        $bus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(\App\Message\ImportTransactionsTask::class))
            ->willReturn(new Envelope((object)['foo' => 'bar']))
        ;

        $starter->start($task);
    }
}
