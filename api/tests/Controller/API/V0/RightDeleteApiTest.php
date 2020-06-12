<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\Entity\Right;
use App\Test\ApiTestCase;

class RightDeleteApiTest extends ApiTestCase
{
    /**
     * @covers \App\Security\RightVoter::supports()
     * @covers \App\Security\RightVoter::voteOnAttribute()
     * @covers \App\DataPersister\RightDataPersister::__construct()
     * @covers \App\DataPersister\RightDataPersister::supports()
     * @covers \App\DataPersister\RightDataPersister::remove()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDeleteRight(): void
    {
        static::createCompanyAdminClient()->request(
            'DELETE',
            $this->findIriBy(
                Right::class,
                ['user' => $this->findUserByEmail('user@doctrine.fixture')->getId()],
            ),
        )
        ;

        static::assertResponseIsSuccessful();
    }

    /**
     * @covers \App\Security\RightVoter::supports()
     * @covers \App\Security\RightVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDeleteRightAnonymous(): void
    {
        static::createClient()->request(
            'DELETE',
            $this->findIriBy(
                Right::class,
                ['user' => $this->findUserByEmail('user@doctrine.fixture')->getId()],
            ),
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
    public function testDeleteRightAsUser(): void
    {
        static::createAuthenticatedClient()->request(
            'DELETE',
            $this->findIriBy(
                Right::class,
                ['user' => $this->findUserByEmail('admin@doctrine.fixture')->getId()],
            ),
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
    public function testDeleteRightOfAnotherAdmin(): void
    {
        static::createAuthenticatedClient()->request(
            'DELETE',
            $this->findIriBy(
                Right::class,
                ['user' => $this->findUserByEmail('second.admin@doctrine.fixture')->getId()],
            ),
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
    public function testDeleteRightOfSelf(): void
    {
        static::createCompanyAdminClient()->request(
            'DELETE',
            $this->findIriBy(
                Right::class,
                ['user' => $this->findUserByEmail('admin@doctrine.fixture')->getId()],
            ),
        )
        ;

        static::assertResponseIsForbidden();
    }
}
