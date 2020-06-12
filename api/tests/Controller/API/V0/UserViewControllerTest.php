<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\Entity\User;
use App\Test\ApiTestCase;

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
        static::createCompanyAdminClient()->request('GET', $this->findIriBy(User::class, ['email' => 'admin@doctrine.fixture']));

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/User',
                '@type' => 'https://schema.org/Person',
                'email' => 'admin@doctrine.fixture',
            ],
            User::class
        );
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewUserAnonymous(): void
    {
        static::createClient()->request('GET', $this->findIriBy(User::class, ['email' => 'admin@doctrine.fixture']));
        static::assertResponseIsForbidden();
    }
}
