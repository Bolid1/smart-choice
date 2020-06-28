<?php

declare(strict_types=1);

namespace App\Message;

use Ramsey\Uuid\UuidInterface;

class ImportTransactionsTask
{
    private UuidInterface $taskId;

    /**
     * ImportTransactionsTask constructor.
     *
     * @param \Ramsey\Uuid\UuidInterface $taskId
     */
    public function __construct(UuidInterface $taskId)
    {
        $this->taskId = $taskId;
    }

    /**
     * @return \Ramsey\Uuid\UuidInterface
     */
    public function getTaskId(): UuidInterface
    {
        return $this->taskId;
    }
}
