<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\DataFixtures\TestsFixtures;
use App\Entity\Company;
use App\Entity\Invitation;
use App\Test\ApiTestCase;

class InvitationPatchApiTest extends ApiTestCase
{
    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchInvitation(): void
    {
        static::createCompanyAdminClient()->request(
            'PATCH',
            $this->getInvitationIri(),
            [
                'json' => [
                    'admin' => $isAdmin = true,
                ],
            ],
        )
        ;

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Invitation',
                '@type' => 'Invitation',
                'admin' => $isAdmin,
            ],
            Invitation::class
        );
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchInvitationAnonymous(): void
    {
        static::createClient()->request(
            'PATCH',
            $this->getInvitationIri(),
            [
                'json' => [
                    'admin' => $isAdmin = true,
                ],
            ],
        )
        ;

        static::assertResponseIsForbidden();
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchInvitationAsUser(): void
    {
        static::createAuthenticatedClient()->request(
            'PATCH',
            $this->getInvitationIri(),
            [
                'json' => [
                    'admin' => $isAdmin = true,
                ],
            ],
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
