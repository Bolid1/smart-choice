<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\Transaction;

use App\Entity\Account;
use App\Entity\Transaction;
use App\Test\ApiTestCase;
use DateTimeInterface;

class TransactionCreateApiTest extends ApiTestCase
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
    public function testCreateTransaction(): void
    {
        // Admin can add any registered user to company
        static::createAuthenticatedClient()->request(
            'POST',
            '/api/v0/transactions',
            [
                'json' => $data = [
                    'account' => $this->findIriBy(
                        Account::class,
                        ['name' => 'Salary card']
                    ),
                    'date' => $date = \date(DateTimeInterface::RFC3339, \strtotime('-3 days 1 hour 15 seconds')),
                    'amount' => 1200.5,
                ],
            ]
        )
        ;

        $this->assertIsString($date, 'Date should be of type string');

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Transaction',
                '@type' => 'https://schema.org/MoneyTransfer',
            ] + $data,
            Transaction::class
        );
    }

    /**
     * @covers \App\Security\TransactionVoter::supports()
     * @covers \App\Security\TransactionVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateTransactionToUnavailableAccount(): void
    {
        // Admin can add any registered user to company
        static::createAuthenticatedClient()->request(
            'POST',
            '/api/v0/transactions',
            [
                'json' => $data = [
                    'account' => $this->findIriBy(
                        Account::class,
                        ['name' => 'Another card']
                    ),
                    'date' => $date = \date(DateTimeInterface::RFC3339, \strtotime('-3 days 1 hour 15 seconds')),
                    'amount' => 1200.5,
                ],
            ]
        )
        ;

        static::assertResponseIsForbidden();
    }
}
