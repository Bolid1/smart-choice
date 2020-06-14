<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\Account;

use App\Entity\Account;
use App\Test\ApiTestCase;

class AccountDeleteApiTest extends ApiTestCase
{
    /**
     * @covers \App\Security\AccountVoter::supports()
     * @covers \App\Security\AccountVoter::voteOnAttribute()
     * @covers \App\DataPersister\AccountDataPersister::__construct()
     * @covers \App\DataPersister\AccountDataPersister::supports()
     * @covers \App\DataPersister\AccountDataPersister::remove()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDeleteAccount(): void
    {
        static::createCompanyAdminClient()->request(
            'DELETE',
            $this->getAccountIri(),
        )
        ;

        static::assertResponseIsSuccessful();
    }

    /**
     * @covers \App\Security\AccountVoter::supports()
     * @covers \App\Security\AccountVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDeleteAccountAnonymous(): void
    {
        static::createClient()->request(
            'DELETE',
            $this->getAccountIri(),
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
    public function testDeleteAccountAsUser(): void
    {
        static::createAuthenticatedClient()->request(
            'DELETE',
            $this->getAccountIri(),
        )
        ;

        static::assertResponseIsForbidden();
    }

    /**
     * @return string|null
     */
    private function getAccountIri(): string
    {
        return $this->findIriBy(
            Account::class,
            [
                'name' => 'Empty account',
            ],
        );
    }
}
