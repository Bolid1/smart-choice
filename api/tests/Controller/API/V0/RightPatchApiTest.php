<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\DataFixtures\TestsFixtures;
use App\Entity\Right;
use App\Test\ApiTestCase;

class RightPatchApiTest extends ApiTestCase
{
    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchRight(): void
    {
        static::createCompanyAdminClient()->request(
            'PATCH',
            $this->findIriBy(
                Right::class,
                ['user' => $this->findUserByEmail(TestsFixtures::USER_EMAIL)->getId()],
            ),
            [
                'json' => [
                    'admin' => $isAdmin = true,
                ],
            ],
        )
        ;

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Right',
                '@type' => 'https://schema.org/Role',
                'admin' => $isAdmin,
            ],
            Right::class
        );
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchRightAnonymous(): void
    {
        static::createClient()->request(
            'PATCH',
            $this->findIriBy(
                Right::class,
                ['user' => $this->findUserByEmail(TestsFixtures::USER_EMAIL)->getId()],
            ),
            [
                'json' => [
                    'admin' => true,
                ],
            ]
        )
        ;

        static::assertResponseIsForbidden();
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchRightAsUser(): void
    {
        static::createAuthenticatedClient()->request(
            'PATCH',
            $this->findIriBy(
                Right::class,
                ['user' => $this->findUserByEmail(TestsFixtures::ADMIN_EMAIL)->getId()],
            ),
            [
                'json' => [
                    'admin' => false,
                ],
            ]
        )
        ;

        static::assertResponseIsForbidden();
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchRightOfSelf(): void
    {
        static::createCompanyAdminClient()->request(
            'PATCH',
            $this->findIriBy(
                Right::class,
                ['user' => $this->findUserByEmail(TestsFixtures::ADMIN_EMAIL)->getId()],
            ),
            [
                'json' => [
                    'admin' => false,
                ],
            ]
        )
        ;

        static::assertResponseIsForbidden();
    }
}
