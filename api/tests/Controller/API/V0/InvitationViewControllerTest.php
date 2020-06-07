<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\DataFixtures\TestsFixtures;
use App\Entity\Company;
use App\Entity\Invitation;
use App\Entity\User;
use App\Test\ApiTestCase;

class InvitationViewControllerTest extends ApiTestCase
{
    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewInvitation(): void
    {
        static::createCompanyAdminClient()->request(
            'GET',
            $this->getInvitationIri(),
        );

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Invitation',
                '@type' => 'Invitation',
                'fromUser' => $this->findIriBy(User::class, ['email' => TestsFixtures::USER_EMAIL]),
                'toCompany' => $this->findIriBy(Company::class, ['name' => TestsFixtures::COMPANY_NAME]),
                'email' => TestsFixtures::ANOTHER_ADMIN_EMAIL,
                'admin' => false,
            ],
            Invitation::class
        );
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewInvitationAnonymous(): void
    {
        static::createClient()->request(
            'GET',
            $this->getInvitationIri(),
        );
        static::assertResponseIsForbidden();
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewInvitationAsUser(): void
    {
        static::createAuthenticatedClient()->request(
            'GET',
            $this->getInvitationIri(),
        );
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
