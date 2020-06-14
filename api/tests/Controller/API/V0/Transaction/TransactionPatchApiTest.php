<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\Transaction;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Test\ApiTestCase;

class TransactionPatchApiTest extends ApiTestCase
{
    /**
     * @covers \App\Security\TransactionVoter::supports()
     * @covers \App\Security\TransactionVoter::voteOnAttribute()
     * @covers \App\DataPersister\TransactionDataPersister::__construct()
     * @covers \App\DataPersister\TransactionDataPersister::supports()
     * @covers \App\DataPersister\TransactionDataPersister::persist()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchTransaction(): void
    {
        $client = static::createAuthenticatedClient();
        $transaction = $this->getTransactionItem();
        $client->request(
            'PATCH',
            $this->getIriFromItem($transaction),
            [
                'json' => [
                    'amount' => $amount = \round($transaction->getAmount() + 100.1, 2),
                ],
            ],
        )
        ;

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Transaction',
                '@type' => 'https://schema.org/MoneyTransfer',
                'amount' => $amount,
            ],
            Transaction::class
        );
    }

    /**
     * @covers \App\Security\TransactionVoter::supports()
     * @covers \App\Security\TransactionVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchTransactionAnonymous(): void
    {
        static::createClient()->request(
            'PATCH',
            $this->getTransactionIri(),
            [
                'json' => [
                    'name' => 'new name',
                ],
            ],
        )
        ;

        static::assertResponseIsForbidden();
    }

    /**
     * @return string|null
     */
    private function getTransactionIri(): ?string
    {
        /** @var Account $account */
        $account = $this->findItemBy(
            Account::class,
            ['name' => 'Salary card']
        );

        return $this->findIriBy(
            Transaction::class,
            [
                'account' => $account->getId(),
            ],
        );
    }

    /**
     * @return Transaction
     */
    private function getTransactionItem(): Transaction
    {
        /** @var Account $account */
        $account = $this->findItemBy(
            Account::class,
            ['name' => 'Salary card']
        );

        /* @noinspection PhpIncompatibleReturnTypeInspection */
        return $this->findItemBy(
            Transaction::class,
            [
                'account' => $account->getId(),
            ],
        );
    }
}
