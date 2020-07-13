<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\TransactionCategory;

use App\Entity\Account;
use App\Entity\TransactionCategory;
use App\Test\ApiTestCase;
use DateTimeInterface;

class TransactionCategoryCreateApiTest extends ApiTestCase
{
    /**
     * @covers \App\Security\Voter\TransactionCategoryVoter::supports()
     * @covers \App\Security\Voter\TransactionCategoryVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateTransactionCategory(): void
    {
        // Admin can add any registered user to company
        static::createAuthenticatedClient()->request(
            'POST',
            '/api/v0/transaction_categories',
            [
                'json' => $data = [
                    'category' => $this->findCategoryIriByCompany(static::COMPANY_NAME),
                    'transaction' => $this->findTransactionIriByCompany(static::COMPANY_NAME),
                    'amount' => 1200.5,
                ],
            ]
        )
        ;

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/TransactionCategory',
                '@type' => 'TransactionCategory',
            ] + $data,
            TransactionCategory::class
        );
    }

    /**
     * @covers \App\Security\Voter\TransactionCategoryVoter::supports()
     * @covers \App\Security\Voter\TransactionCategoryVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateTransactionCategoryToUnavailableAccount(): void
    {
        // Admin can add any registered user to company
        static::createAuthenticatedClient()->request(
            'POST',
            '/api/v0/transaction_categories',
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
