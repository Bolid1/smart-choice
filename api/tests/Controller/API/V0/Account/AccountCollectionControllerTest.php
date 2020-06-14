<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\Account;

use App\Entity\Account;
use App\Test\ApiTestCase;

class AccountCollectionControllerTest extends ApiTestCase
{
    /**
     * @covers \App\Security\AccountExtension::__construct()
     * @covers \App\Security\AccountExtension::applyToCollection()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testAdminGetAccountCollection(): void
    {
        static::createCompanyAdminClient()->request('GET', '/api/v0/accounts');
        static::assertResponseIsSuccessfulCollectionJsonSchema(
            [
                '@context' => '/api/v0/contexts/Account',
                '@id' => '/api/v0/accounts',
                '@type' => 'hydra:Collection',
                // Admin can see his accounts, and invitations of companies, where he is admin.
                'hydra:member' => [
                    [
                        '@type' => 'https://schema.org/BankAccount',
                        'currency' => 'RUB',
                    ],
                ],
                'hydra:totalItems' => 2,
            ],
            Account::class
        );
    }

    /**
     * @covers \App\Security\AccountExtension::__construct()
     * @covers \App\Security\AccountExtension::applyToCollection()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testUserGetAccountCollection(): void
    {
        static::createAuthenticatedClient()->request('GET', '/api/v0/accounts');
        static::assertResponseIsSuccessfulCollectionJsonSchema(
            [
                '@context' => '/api/v0/contexts/Account',
                '@id' => '/api/v0/accounts',
                '@type' => 'hydra:Collection',
                // User can see his accounts
                'hydra:member' => [
                    [
                        '@type' => 'https://schema.org/BankAccount',
                        'currency' => 'RUB',
                    ],
                ],
                'hydra:totalItems' => 2,
            ],
            Account::class
        );
    }

    /**
     * @covers \App\Security\AccountExtension::__construct()
     * @covers \App\Security\AccountExtension::applyToCollection()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testGetCollectionAnonymous(): void
    {
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        static::createClient()->request('GET', '/api/v0/accounts');
        static::assertResponseIsForbidden();
    }
}
