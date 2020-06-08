<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\DataFixtures\TestsFixtures;
use App\Entity\Company;
use App\Entity\Invitation;
use App\Test\ApiTestCase;

class InvitationAcceptApiTest extends ApiTestCase
{
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
    public function testAcceptInvitation(): void
    {
        $client = static::createCompanyAdminClient();

        $client->request('GET', $company = $this->getCompanyIri());
        static::assertResponseIsForbidden();

        $client->request(
            'POST',
            "{$this->getInvitationIri()}/accept",
            [
                'json' => [
                    'plainSecret' => TestsFixtures::ADMIN_INVITATION_SECRET,
                ],
            ],
        )
        ;

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Invitation',
                '@type' => 'Invitation',
            ],
            Invitation::class
        );

        $client->request('GET', $company = $this->getCompanyIri());
        static::assertResponseIsSuccessful();
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
        $client = static::createAnotherAdminClient();

        $client->request('GET', $company = $this->getCompanyIri());
        static::assertResponseIsForbidden();

        $client->request(
            'POST',
            "{$this->getInvitationIri()}/accept",
            [
                'json' => [
                    'plainSecret' => 'Invalid secret',
                ],
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
            'POST',
            "{$this->getInvitationIri()}/accept",
            [
                'json' => [
                    'plainSecret' => TestsFixtures::ADMIN_INVITATION_SECRET,
                ],
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
            'POST',
            "{$this->getInvitationIri()}/accept",
            [
                'json' => [
                    'plainSecret' => TestsFixtures::ADMIN_INVITATION_SECRET,
                ],
            ],
        )
        ;

        static::assertResponseIsForbidden();
    }

    private function getCompanyIri(): string
    {
        return $this->findIriBy(Company::class, ['name' => TestsFixtures::ANOTHER_COMPANY_NAME]);
    }

    /**
     * @return string|null
     */
    private function getInvitationIri(): ?string
    {
        /** @var Company $company */
        $company = $this->findItemBy(
            Company::class,
            ['name' => TestsFixtures::ANOTHER_COMPANY_NAME]
        );

        return $this->findIriBy(
            Invitation::class,
            [
                'toCompany' => $company->getId(),
                'email' => TestsFixtures::ADMIN_EMAIL,
            ],
        );
    }
}
