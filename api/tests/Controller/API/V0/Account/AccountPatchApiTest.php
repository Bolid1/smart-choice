<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\Account;

use App\Entity\Account;
use App\Test\ApiTestCase;

class AccountPatchApiTest extends ApiTestCase
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
    public function testPatchAccount(): void
    {
        static::createCompanyAdminClient()->request(
            'PATCH',
            $this->getAccountIri(),
            [
                'json' => [
                    'name' => $name = 'new name',
                ],
            ],
        )
        ;

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Account',
                '@type' => 'https://schema.org/BankAccount',
                'name' => $name,
            ],
            Account::class
        );
    }

    /**
     * @covers \App\Security\AccountVoter::supports()
     * @covers \App\Security\AccountVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchAccountAnonymous(): void
    {
        static::createClient()->request(
            'PATCH',
            $this->getAccountIri(),
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
     * @covers \App\Security\AccountVoter::supports()
     * @covers \App\Security\AccountVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchAccountAsUser(): void
    {
        static::createAuthenticatedClient()->request(
            'PATCH',
            $this->getAccountIri(),
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
    private function getAccountIri(): ?string
    {
        return $this->findIriBy(
            Account::class,
            [
                'name' => 'Salary card',
            ],
        );
    }
}
