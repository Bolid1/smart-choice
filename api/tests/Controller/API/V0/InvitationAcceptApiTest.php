<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\Entity\Company;
use App\Entity\Invitation;
use App\Test\ApiTestCase;

class InvitationAcceptApiTest extends ApiTestCase
{
    /**
     * @covers \App\Controller\API\V0\AcceptInvitation::__invoke()
     * @covers \App\Security\InvitationVoter::__construct()
     * @covers \App\Security\InvitationVoter::supports()
     * @covers \App\Security\InvitationVoter::voteOnAttribute()
     * @covers \App\DataPersister\InvitationDataPersister::__construct()
     * @covers \App\DataPersister\InvitationDataPersister::supports()
     * @covers \App\DataPersister\InvitationDataPersister::persist()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testAcceptInvitation(): void
    {
        $client = static::createCompanyAdminClient();

        $client->request('GET', $company = $this->getCompanyIri());
        static::assertResponseIsForbidden();

        $client->request(
            'DELETE',
            "{$this->getInvitationIri()}/accept",
            [
                'body' => 'Another secret',
            ],
        )
        ;

        static::assertResponseIsSuccessful();

        $client->request('GET', $company = $this->getCompanyIri());
        static::assertResponseIsSuccessful();

        $this->assertNull($this->getInvitationIri(), 'Invitation should be deleted after accept');
    }

    /**
     * @covers \App\Security\InvitationVoter::__construct()
     * @covers \App\Security\InvitationVoter::supports()
     * @covers \App\Security\InvitationVoter::voteOnAttribute()
     * @covers \App\DataPersister\InvitationDataPersister::__construct()
     * @covers \App\DataPersister\InvitationDataPersister::supports()
     * @covers \App\DataPersister\InvitationDataPersister::persist()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testAcceptInvitationWithInvalidSecret(): void
    {
        $client = static::createCompanyAdminClient();

        $client->request('GET', $company = $this->getCompanyIri());
        static::assertResponseIsForbidden();

        $client->request(
            'DELETE',
            "{$this->getInvitationIri()}/accept",
            [
                'body' => 'Invalid secret',
            ],
        )
        ;

        static::assertResponseIsInvalidParams();

        $client->request('GET', $company = $this->getCompanyIri());
        static::assertResponseIsForbidden();
    }

    /**
     * @covers \App\Security\InvitationVoter::__construct()
     * @covers \App\Security\InvitationVoter::supports()
     * @covers \App\Security\InvitationVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testAcceptInvitationOfAnotherUser(): void
    {
        $client = static::createAuthenticatedClient();

        $client->request('GET', $this->getCompanyIri());
        static::assertResponseIsForbidden();

        $client->request(
            'DELETE',
            "{$this->getInvitationIri()}/accept",
            [
                'body' => 'Another secret',
            ],
        )
        ;
        static::assertResponseIsForbidden();

        $client->request('GET', $this->getCompanyIri());
        static::assertResponseIsForbidden();
    }

    /**
     * @covers \App\Security\InvitationVoter::__construct()
     * @covers \App\Security\InvitationVoter::supports()
     * @covers \App\Security\InvitationVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testAcceptInvitationAnonymous(): void
    {
        static::createClient()->request(
            'DELETE',
            "{$this->getInvitationIri()}/accept",
            [
                'body' => 'Another secret',
            ],
        )
        ;

        static::assertResponseIsForbidden();
    }

    private function getCompanyIri(): string
    {
        return $this->findIriBy(Company::class, ['name' => 'Corporation LTD']);
    }

    /**
     * @return string|null
     */
    private function getInvitationIri(): ?string
    {
        /** @var Company $company */
        $company = $this->findItemBy(
            Company::class,
            ['name' => 'Corporation LTD']
        );

        return $this->findIriBy(
            Invitation::class,
            [
                'toCompany' => $company->getId(),
                'email' => 'admin@doctrine.fixture',
            ],
        );
    }
}
