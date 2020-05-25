<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\DataFixtures\UserFixtures;
use App\Entity\User;

class UserViewControllerTest extends ApiTestCase
{
    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewUser(): void
    {
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        $client = static::createClient();
        $client->request(
            'POST',
            '/api/v0/auth/json',
            [
                'json' => [
                    'email' => UserFixtures::EMAIL,
                    'password' => UserFixtures::PASSWORD,
                ],
            ],
        );
        $client->request('GET', $this->findIriBy(User::class, ['email' => UserFixtures::EMAIL]));

        static::assertResponseIsSuccessful();
        static::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        static::assertJsonContains(
            [
                '@context' => '/api/v0/contexts/User',
                '@type' => 'https://schema.org/Person',
                'email' => UserFixtures::EMAIL,
            ]
        );

        // Asserts that the returned JSON is validated by the JSON Schema generated for this resource by API Platform
        // This generated JSON Schema is also used in the OpenAPI spec!
        static::assertMatchesResourceCollectionJsonSchema(User::class);
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewUserAnonymous(): void
    {
        $client = static::createClient();
        $client->request('GET', $this->findIriBy(User::class, ['email' => UserFixtures::EMAIL]));

        static::assertResponseStatusCodeSame(404);
        static::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
    }
}
