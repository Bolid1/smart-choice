<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\DataFixtures\TestsFixtures;
use App\Entity\User;
use App\Test\ApiTestCase;

class UserPatchApiTest extends ApiTestCase
{
    /**
     * @covers \App\DataPersister\UserDataPersister::__construct()
     * @covers \App\DataPersister\UserDataPersister::supports()
     * @covers \App\DataPersister\UserDataPersister::persist()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchUser(): void
    {
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        static::createCompanyAdminClient()->request(
            'PATCH',
            $this->findIriBy(User::class, ['email' => TestsFixtures::ADMIN_EMAIL]),
            [
                'json' => [
                    'plainPassword' => $password = 'my_new_password',
                ],
            ]
        );

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/User',
                '@type' => 'https://schema.org/Person',
                'email' => TestsFixtures::ADMIN_EMAIL,
            ],
            User::class
        );

        // Try to use new password
        static::createClient()->request(
            'POST',
            '/api/v0/auth/json',
            [
                'json' => [
                    'email' => TestsFixtures::ADMIN_EMAIL,
                    'password' => $password,
                ],
            ],
        )
        ;

        static::assertResponseIsSuccessful();
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchUserAnonymous(): void
    {
        static::createClient()->request(
            'PATCH',
            $this->findIriBy(User::class, ['email' => TestsFixtures::ADMIN_EMAIL]),
            [
                'json' => [
                    'plainPassword' => 'my_new_password',
                ],
            ]
        );

        static::assertResponseIsForbidden();
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchUserInvalid(): void
    {
        static::createCompanyAdminClient()->request(
            'PATCH',
            $this->findIriBy(User::class, ['email' => TestsFixtures::ADMIN_EMAIL]),
            [
                'json' => [
                    'plainPassword' => 'short',
                ],
            ]
        );

        static::assertResponseIsInvalidParams(
            [
                '@context' => '/api/v0/contexts/ConstraintViolationList',
                '@type' => 'ConstraintViolationList',
                'hydra:title' => 'An error occurred',
            ]
        );
    }
}
