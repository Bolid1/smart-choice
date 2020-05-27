<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\User;

class UserCreateControllerTest extends ApiTestCase
{
    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateUser(): void
    {
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        static::createClient()->request(
            'POST',
            '/api/v0/users',
            [
                'json' => [
                    'email' => $email = 'foo@bar.bax',
                    'plainPassword' => $password = 'password',
                ],
            ]
        )
        ;

        static::assertResponseIsSuccessful();
        static::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        static::assertJsonContains(
            [
                '@context' => '/api/v0/contexts/User',
                '@type' => 'https://schema.org/Person',
                'email' => $email,
            ]
        );

        // Asserts that the returned JSON is validated by the JSON Schema generated for this resource by API Platform
        // This generated JSON Schema is also used in the OpenAPI spec!
        static::assertMatchesResourceCollectionJsonSchema(User::class);

        // Check that user can authenticate later
        static::createClient()->request(
            'POST',
            '/api/v0/auth/json',
            ['json' => \compact('email', 'password')]
        )
        ;

        static::assertResponseIsSuccessful();
    }

    /**
     * @dataProvider invalidCredentialsProvider
     *
     * @param string $email
     * @param string $password
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateInvalidUser(string $email, string $password): void
    {
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        static::createClient()->request(
            'POST',
            '/api/v0/users',
            [
                'json' => [
                    'email' => $email,
                    'plainPassword' => $password,
                ],
            ]
        )
        ;

        static::assertResponseStatusCodeSame(400);
        static::assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');

        static::assertJsonContains(
            [
                '@context' => '/api/v0/contexts/ConstraintViolationList',
                '@type' => 'ConstraintViolationList',
                'hydra:title' => 'An error occurred',
            ]
        );
    }

    public function invalidCredentialsProvider(): array
    {
        return [
            ['invalid_email', 'password'],
            ['foo@bar.baz', 'short'],
        ];
    }
}
