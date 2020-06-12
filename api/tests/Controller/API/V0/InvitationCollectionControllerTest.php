<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\DataFixtures\TestsFixtures;
use App\Entity\Invitation;
use App\Test\ApiTestCase;

class InvitationCollectionControllerTest extends ApiTestCase
{
    /**
     * @covers \App\Security\InvitationExtension::__construct()
     * @covers \App\Security\InvitationExtension::applyToCollection()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testAdminGetInvitationCollection(): void
    {
        static::createCompanyAdminClient()->request('GET', '/api/v0/invitations');
        static::assertResponseIsSuccessfulCollectionJsonSchema(
            [
                '@context' => '/api/v0/contexts/Invitation',
                '@id' => '/api/v0/invitations',
                '@type' => 'hydra:Collection',
                // Admin can see his invitations, and invitations of companies, where he is admin.
                'hydra:member' => [
                    [
                        '@type' => 'Invitation',
                        'email' => TestsFixtures::ANOTHER_ADMIN_EMAIL,
                        'admin' => false,
                    ],
                ],
                'hydra:totalItems' => 1,
            ],
            Invitation::class
        );
    }

    /**
     * @covers \App\Security\InvitationExtension::__construct()
     * @covers \App\Security\InvitationExtension::applyToCollection()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testUserGetInvitationCollection(): void
    {
        static::createAuthenticatedClient()->request('GET', '/api/v0/invitations');
        static::assertResponseIsSuccessfulCollectionJsonSchema(
            [
                '@context' => '/api/v0/contexts/Invitation',
                '@id' => '/api/v0/invitations',
                '@type' => 'hydra:Collection',
                // We are logged in, so we can see self properties
                'hydra:member' => [],
                'hydra:totalItems' => 0,
            ],
            Invitation::class
        );
    }

    /**
     * @covers \App\Security\InvitationExtension::__construct()
     * @covers \App\Security\InvitationExtension::applyToCollection()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testGetCollectionAnonymous(): void
    {
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        static::createClient()->request('GET', '/api/v0/invitations');
        static::assertResponseIsForbidden();
    }
}
