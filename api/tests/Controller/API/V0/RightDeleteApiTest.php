<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\DataFixtures\TestsFixtures;
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
                ['user' => $this->findUserByEmail(TestsFixtures::USER_EMAIL)->getId()],
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
                ['user' => $this->findUserByEmail(TestsFixtures::USER_EMAIL)->getId()],
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
                ['user' => $this->findUserByEmail(TestsFixtures::ADMIN_EMAIL)->getId()],
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
                ['user' => $this->findUserByEmail(TestsFixtures::SECOND_ADMIN_EMAIL)->getId()],
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
                ['user' => $this->findUserByEmail(TestsFixtures::ADMIN_EMAIL)->getId()],
            ),
        )
        ;

        static::assertResponseIsForbidden();
    }
}
