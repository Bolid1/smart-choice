<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\DataFixtures\TestsFixtures;
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
        static::createCompanyAdminClient()->request('GET', $this->findIriBy(User::class, ['email' => TestsFixtures::ADMIN_EMAIL]));

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/User',
                '@type' => 'https://schema.org/Person',
                'email' => TestsFixtures::ADMIN_EMAIL,
            ],
            User::class
        );
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewUserAnonymous(): void
    {
        static::createClient()->request('GET', $this->findIriBy(User::class, ['email' => TestsFixtures::ADMIN_EMAIL]));
        static::assertResponseIsForbidden();
    }
}
