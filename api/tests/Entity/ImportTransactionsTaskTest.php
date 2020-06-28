<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\ImportTransactionsTask;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class ImportTransactionsTaskTest extends TestCase
{
    private ImportTransactionsTask $task;

    protected function setUp(): void
    {
        $this->task = new ImportTransactionsTask();
    }

    /**
     * @covers \App\Entity\ImportTransactionsTask::getScheduledTime()
     * @covers \App\Entity\ImportTransactionsTask::setScheduledTime()
     */
    public function testScheduledTime(): void
    {
        $this->assertNull($this->task->getScheduledTime());
        $this->assertSame($this->task, $this->task->setScheduledTime($date = new DateTimeImmutable()));
        $this->assertSame($date, $this->task->getScheduledTime());
    }

    /**
     * @covers \App\Entity\ImportTransactionsTask::getStartTime()
     * @covers \App\Entity\ImportTransactionsTask::setStartTime()
     */
    public function testStartTime(): void
    {
        $this->assertNull($this->task->getStartTime());
        $this->assertSame($this->task, $this->task->setStartTime($date = new DateTimeImmutable()));
        $this->assertSame($date, $this->task->getStartTime());
    }

    /**
     * @covers \App\Entity\ImportTransactionsTask::getEndTime()
     * @covers \App\Entity\ImportTransactionsTask::setEndTime()
     */
    public function testEndTime(): void
    {
        $this->assertNull($this->task->getEndTime());
        $this->assertSame($this->task, $this->task->setEndTime($date = new DateTimeImmutable()));
        $this->assertSame($date, $this->task->getEndTime());
    }

    /**
     * @covers \App\Entity\ImportTransactionsTask::beforeSchedule()
     */
    public function testBeforeSchedule(): void
    {
        $this->assertNull($this->task->getScheduledTime());

        $this->task->beforeSchedule();

        $this->assertEquals(\time(), $this->task->getScheduledTime()->getTimestamp());
    }

    /**
     * @covers \App\Entity\ImportTransactionsTask::onStart()
     */
    public function testOnStart(): void
    {
        $this->task->failedToImport = 32;
        $this->task->successfullyImported = 12;
        $this->assertEquals(ImportTransactionsTask::STATUS_ACCEPTED, $this->task->status);
        $this->assertNull($this->task->errors);

        $this->task->onStart();

        $this->assertEquals(\time(), $this->task->getStartTime()->getTimestamp());
        $this->assertEquals(0, $this->task->successfullyImported);
        $this->assertEquals(0, $this->task->failedToImport);
        $this->assertEquals(ImportTransactionsTask::STATUS_STARTED, $this->task->status);
        $this->assertIsArray($this->task->errors);
    }

    /**
     * @covers \App\Entity\ImportTransactionsTask::onFinish()
     */
    public function testOnFinish(): void
    {
        $this->task->onStart();
        $this->task->successfullyImported = $successfullyImported = 12;
        $this->task->failedToImport = $failedToImport = 32;
        $this->task->errors = $errors = ['some' => 'error'];

        $this->task->onFinish($lastErrors = ['foo' => 'bar']);

        $this->assertEquals(\time(), $this->task->getStartTime()->getTimestamp());
        $this->assertEquals($successfullyImported, $this->task->successfullyImported);
        $this->assertEquals($failedToImport, $this->task->failedToImport);
        $this->assertEquals(ImportTransactionsTask::STATUS_FINISHED, $this->task->status);
        $this->assertEquals($errors + $lastErrors, $this->task->errors);
    }
}
