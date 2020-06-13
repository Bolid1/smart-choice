<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\Account;

use App\Entity\Account;
use App\Entity\Company;
use App\Test\ApiTestCase;

class AccountCreateApiTest extends ApiTestCase
{
    /**
     * @covers \App\Security\AccountVoter::supports()
     * @covers \App\Security\AccountVoter::voteOnAttribute()
     * @covers \App\DataPersister\AccountDataPersister::__construct()
     * @covers \App\DataPersister\AccountDataPersister::supports()
     * @covers \App\DataPersister\AccountDataPersister::persist()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateAccount(): void
    {
        // Admin can add any registered user to company
        static::createCompanyAdminClient()->request(
            'POST',
            '/api/v0/accounts',
            [
                'json' => $data = [
                    'company' => $company = $this->findIriBy(
                        Company::class,
                        ['name' => 'Richards family']
                    ),
                    'currency' => $currency = 'RUB',
                    'name' => $name = 'Second card',
                    'balance' => $balance = 123546.67,
                ],
            ]
        )
        ;

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Account',
                '@type' => 'https://schema.org/BankAccount',
            ] + $data,
            Account::class
        );
    }

    /**
     * @covers \App\Security\AccountVoter::supports()
     * @covers \App\Security\AccountVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateAccountAsUser(): void
    {
        // Admin can add any registered user to company
        static::createAuthenticatedClient()->request(
            'POST',
            '/api/v0/accounts',
            [
                'json' => [
                    'company' => $company = $this->findIriBy(
                        Company::class,
                        ['name' => 'Richards family']
                    ),
                    'currency' => $currency = 'RUB',
                    'name' => $name = 'Second card',
                    'balance' => $balance = 123546.67,
                ],
            ]
        )
        ;

        static::assertResponseIsForbidden();
    }

    /**
     * @covers \App\Security\AccountVoter::supports()
     * @covers \App\Security\AccountVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateAccountToAnotherCompany(): void
    {
        // Admin can add any registered user to company
        static::createCompanyAdminClient()->request(
            'POST',
            '/api/v0/accounts',
            [
                'json' => [
                    'company' => $company = $this->findIriBy(
                        Company::class,
                        ['name' => 'Corporation LTD']
                    ),
                    'currency' => $currency = 'RUB',
                    'name' => $name = 'Second card',
                    'balance' => $balance = 123546.67,
                ],
            ]
        )
        ;

        static::assertResponseIsForbidden();
    }
}
