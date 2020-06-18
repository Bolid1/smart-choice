<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\Account;

use App\Entity\Account;
use App\Test\ApiTestCase;

class AccountViewControllerTest extends ApiTestCase
{
    /**
     * @covers \App\Security\AccountVoter::supports()
     * @covers \App\Security\AccountVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewAccount(): void
    {
        static::createCompanyAdminClient()->request(
            'GET',
            $this->getAccountIri(),
        );

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Account',
                '@type' => 'https://schema.org/BankAccount',
                'name' => 'Salary card',
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
    public function testViewAccountAnonymous(): void
    {
        static::createClient()->request(
            'GET',
            $this->getAccountIri(),
        );
        static::assertResponseIsForbidden();
    }

    /**
     * @covers \App\Security\AccountVoter::supports()
     * @covers \App\Security\AccountVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewAccountAsUser(): void
    {
        static::createAuthenticatedClient()->request(
            'GET',
            $this->getAccountIri(),
        );

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Account',
                '@type' => 'https://schema.org/BankAccount',
                'name' => 'Salary card',
            ],
            Account::class
        );
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
