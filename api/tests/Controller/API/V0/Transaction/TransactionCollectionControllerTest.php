<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\Transaction;

use App\Entity\Transaction;
use App\Test\ApiTestCase;

class TransactionCollectionControllerTest extends ApiTestCase
{
    /**
     * @covers \App\Security\TransactionExtension::__construct()
     * @covers \App\Security\TransactionExtension::applyToCollection()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testAdminGetTransactionCollection(): void
    {
        static::createAuthenticatedClient()->request('GET', '/api/v0/transactions');
        static::assertResponseIsSuccessfulCollectionJsonSchema(
            [
                '@context' => '/api/v0/contexts/Transaction',
                '@id' => '/api/v0/transactions',
                '@type' => 'hydra:Collection',
                // Admin can see his transactions, and invitations of companies, where he is admin.
                'hydra:member' => [
                    [
                        '@type' => 'https://schema.org/MoneyTransfer',
                    ],
                ],
                'hydra:totalItems' => 10,
            ],
            Transaction::class
        );
    }

    /**
     * @covers \App\Security\TransactionExtension::__construct()
     * @covers \App\Security\TransactionExtension::applyToCollection()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testGetCollectionAnonymous(): void
    {
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        static::createClient()->request('GET', '/api/v0/transactions');
        static::assertResponseIsForbidden();
    }
}
