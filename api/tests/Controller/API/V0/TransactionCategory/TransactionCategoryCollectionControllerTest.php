<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\TransactionCategory;

use App\Entity\TransactionCategory;
use App\Test\ApiTestCase;

class TransactionCategoryCollectionControllerTest extends ApiTestCase
{
    /**
     * @covers \App\Security\Extension\TransactionCategoryExtension::__construct()
     * @covers \App\Security\Extension\TransactionCategoryExtension::applyToCollection()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testAdminGetTransactionCategoryCollection(): void
    {
        static::createAuthenticatedClient()->request('GET', '/api/v0/transaction_categories');
        static::assertResponseIsSuccessfulCollectionJsonSchema(
            [
                '@context' => '/api/v0/contexts/TransactionCategory',
                '@id' => '/api/v0/transaction_categories',
                '@type' => 'hydra:Collection',
                // Admin can see his transactionCategories, and invitations of companies, where he is admin.
                'hydra:member' => [
                    [
                        '@type' => 'TransactionCategory',
                    ],
                ],
                'hydra:totalItems' => 2,
            ],
            TransactionCategory::class
        );
    }

    /**
     * @covers \App\Security\Extension\TransactionCategoryExtension::__construct()
     * @covers \App\Security\Extension\TransactionCategoryExtension::applyToCollection()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testGetCollectionAnonymous(): void
    {
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        static::createClient()->request('GET', '/api/v0/transaction_categories');
        static::assertResponseIsForbidden();
    }
}
