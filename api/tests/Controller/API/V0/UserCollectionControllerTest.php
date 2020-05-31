<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\DataFixtures\TestsFixtures;
use App\Entity\User;
use App\Test\ApiTestCase;

class UserCollectionControllerTest extends ApiTestCase
{
    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testGetUserCollection(): void
    {
        static::createCompanyAdminClient()->request('GET', '/api/v0/users');
        static::assertResponseIsSuccessfulCollectionJsonSchema(
            [
                '@context' => '/api/v0/contexts/User',
                '@id' => '/api/v0/users',
                '@type' => 'hydra:Collection',
                // We are logged in, so we can see self properties
                'hydra:member' => [
                    [
                        'email' => TestsFixtures::ADMIN_EMAIL,
                    ],
                ],
                'hydra:totalItems' => 1,
            ],
            User::class
        );
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testGetCollectionAnonymous(): void
    {
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        static::createClient()->request('GET', '/api/v0/users');
        static::assertResponseIsForbidden();
    }
}
