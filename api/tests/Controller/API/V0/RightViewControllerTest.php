<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\DataFixtures\TestsFixtures;
use App\Entity\Right;
use App\Entity\User;
use App\Test\ApiTestCase;

class RightViewControllerTest extends ApiTestCase
{
    /**
     * @covers \App\Security\RightVoter::supports()
     * @covers \App\Security\RightVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewRight(): void
    {
        static::createCompanyAdminClient()->request(
            'GET',
            $this->findIriBy(
                Right::class,
                ['user' => $this->findUserByEmail(TestsFixtures::ADMIN_EMAIL)->getId()],
            ),
        );

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Right',
                '@type' => 'https://schema.org/Role',
                'user' => $this->findIriBy(User::class, ['email' => TestsFixtures::ADMIN_EMAIL]),
                'admin' => true,
            ],
            Right::class
        );
    }

    /**
     * @covers \App\Security\RightVoter::supports()
     * @covers \App\Security\RightVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewRightOfSelf(): void
    {
        static::createAuthenticatedClient()->request(
            'GET',
            $this->findIriBy(
                Right::class,
                ['user' => $this->findUserByEmail(TestsFixtures::USER_EMAIL)->getId()],
            ),
        );

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Right',
                '@type' => 'https://schema.org/Role',
                'user' => $this->findIriBy(User::class, ['email' => TestsFixtures::USER_EMAIL]),
                'admin' => false,
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
    public function testViewRightAnonymous(): void
    {
        static::createClient()->request(
            'GET',
            $this->findIriBy(
                Right::class,
                ['user' => $this->findUserByEmail(TestsFixtures::ADMIN_EMAIL)->getId()],
            ),
        );
        static::assertResponseIsForbidden();
    }

    /**
     * @covers \App\Security\RightVoter::supports()
     * @covers \App\Security\RightVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewRightAsUser(): void
    {
        static::createAuthenticatedClient()->request(
            'GET',
            $this->findIriBy(
                Right::class,
                ['user' => $this->findUserByEmail(TestsFixtures::ADMIN_EMAIL)->getId()],
            ),
        );
        static::assertResponseIsForbidden();
    }
}
