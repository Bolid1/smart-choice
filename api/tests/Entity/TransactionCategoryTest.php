<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Company;
use App\Entity\Transaction;
use App\Entity\TransactionCategory;
use PHPUnit\Framework\TestCase;

class TransactionCategoryTest extends TestCase
{
    /**
     * @covers \App\Entity\TransactionCategory::getTransaction()
     * @covers \App\Entity\TransactionCategory::getCompany()
     * @covers \App\Entity\TransactionCategory::setTransaction()
     */
    public function testSetTransaction(): void
    {
        $entity = new TransactionCategory();
        $transaction = new Transaction();
        $transaction->setCompany($company = new Company());

        self::assertNull($entity->getTransaction());
        self::assertNull($entity->getCompany());

        $entity->setTransaction($transaction);
        self::assertSame($transaction, $entity->getTransaction());
        self::assertSame($company, $entity->getCompany());
    }
}
