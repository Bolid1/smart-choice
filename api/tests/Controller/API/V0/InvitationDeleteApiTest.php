<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\DataFixtures\TestsFixtures;
use App\Entity\Company;
use App\Entity\Invitation;
use App\Test\ApiTestCase;

class InvitationDeleteApiTest extends ApiTestCase
{
    /**
     * @covers \App\Security\InvitationVoter::__construct()
     * @covers \App\Security\InvitationVoter::supports()
     * @covers \App\Security\InvitationVoter::voteOnAttribute()
     * @covers \App\DataPersister\InvitationDataPersister::__construct()
     * @covers \App\DataPersister\InvitationDataPersister::supports()
     * @covers \App\DataPersister\InvitationDataPersister::remove()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDeleteInvitation(): void
    {
        static::createCompanyAdminClient()->request(
            'DELETE',
            $this->getInvitationIri(),
        )
        ;

        static::assertResponseIsSuccessful();
    }

    /**
     * @covers \App\Security\InvitationVoter::__construct()
     * @covers \App\Security\InvitationVoter::supports()
     * @covers \App\Security\InvitationVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDeleteInvitationAnonymous(): void
    {
        static::createClient()->request(
            'DELETE',
            $this->getInvitationIri(),
        )
        ;

        static::assertResponseIsForbidden();
    }

    /**
     * @covers \App\Security\InvitationVoter::__construct()
     * @covers \App\Security\InvitationVoter::supports()
     * @covers \App\Security\InvitationVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDeleteInvitationAsUser(): void
    {
        static::createAuthenticatedClient()->request(
            'DELETE',
            $this->getInvitationIri(),
        )
        ;

        static::assertResponseIsForbidden();
    }

    /**
     * @return string|null
     */
    private function getInvitationIri(): ?string
    {
        /** @var Company $company */
        $company = $this->findItemBy(
            Company::class,
            ['name' => TestsFixtures::COMPANY_NAME]
        );

        return $this->findIriBy(
            Invitation::class,
            [
                'toCompany' => $company->getId(),
                'email' => TestsFixtures::ANOTHER_ADMIN_EMAIL,
            ],
        );
    }
}
