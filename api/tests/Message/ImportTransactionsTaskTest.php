<?php

declare(strict_types=1);

namespace App\Tests\Message;

use App\Message\ImportTransactionsTask;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;

class ImportTransactionsTaskTest extends TestCase
{
    /**
     * @covers \App\Message\ImportTransactionsTask::__construct
     * @covers \App\Message\ImportTransactionsTask::getTaskId
     */
    public function testGetTaskId(): void
    {
        $message = new ImportTransactionsTask($uuid = $this->createMock(UuidInterface::class));
        $this->assertSame($uuid, $message->getTaskId());
    }
}
