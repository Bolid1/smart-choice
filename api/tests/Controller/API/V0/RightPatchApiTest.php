<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\Entity\Right;
use App\Test\ApiTestCase;

class RightPatchApiTest extends ApiTestCase
{
    /**
     * @covers \App\Security\RightVoter::supports()
     * @covers \App\Security\RightVoter::voteOnAttribute()
     * @covers \App\DataPersister\RightDataPersister::__construct()
     * @covers \App\DataPersister\RightDataPersister::supports()
     * @covers \App\DataPersister\RightDataPersister::persist()
     *
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
                ['user' => $this->findUserByEmail('user@doctrine.fixture')->getId()],
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
     * @covers \App\Security\RightVoter::supports()
     * @covers \App\Security\RightVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchRightAnonymous(): void
    {
        static::createClient()->request(
            'PATCH',
            $this->findIriBy(
                Right::class,
                ['user' => $this->findUserByEmail('user@doctrine.fixture')->getId()],
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
     * @covers \App\Security\RightVoter::supports()
     * @covers \App\Security\RightVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchRightAsUser(): void
    {
        static::createAuthenticatedClient()->request(
            'PATCH',
            $this->findIriBy(
                Right::class,
                ['user' => $this->findUserByEmail('admin@doctrine.fixture')->getId()],
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
     * @covers \App\Security\RightVoter::supports()
     * @covers \App\Security\RightVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchRightOfSelf(): void
    {
        static::createCompanyAdminClient()->request(
            'PATCH',
            $this->findIriBy(
                Right::class,
                ['user' => $this->findUserByEmail('admin@doctrine.fixture')->getId()],
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
