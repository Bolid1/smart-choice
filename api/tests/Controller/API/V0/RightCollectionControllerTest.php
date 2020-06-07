<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\DataFixtures\TestsFixtures;
use App\Entity\Right;
use App\Entity\User;
use App\Test\ApiTestCase;

class RightCollectionControllerTest extends ApiTestCase
{
    /**
     * @covers \App\Security\RightExtension::__construct()
     * @covers \App\Security\RightExtension::applyToCollection()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testAdminGetRightCollection(): void
    {
        $response = static::createCompanyAdminClient()->request('GET', '/api/v0/rights');

        $expected = [
            $this->findIriBy(User::class, ['email' => TestsFixtures::ADMIN_EMAIL]),
            $this->findIriBy(User::class, ['email' => TestsFixtures::USER_EMAIL]),
            $this->findIriBy(User::class, ['email' => TestsFixtures::SECOND_ADMIN_EMAIL]),
        ];

        static::assertResponseIsSuccessfulCollectionJsonSchema(
            [
                '@context' => '/api/v0/contexts/Right',
                '@id' => '/api/v0/rights',
                '@type' => 'hydra:Collection',
                // Admin can see his rights, and rights of companies, where he is admin.
                'hydra:member' => [],
                'hydra:totalItems' => \count($expected),
            ],
            Right::class
        );

        $data = $response->toArray(false);
        $this->assertIsArray($data);
        $this->assertArrayHasKey('hydra:member', $data);

        $this->assertEquals($expected, \array_intersect($expected, \array_column($data['hydra:member'], 'user')));
    }

    /**
     * @covers \App\Security\RightExtension::__construct()
     * @covers \App\Security\RightExtension::applyToCollection()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testUserGetRightCollection(): void
    {
        static::createAuthenticatedClient()->request('GET', '/api/v0/rights');
        static::assertResponseIsSuccessfulCollectionJsonSchema(
            [
                '@context' => '/api/v0/contexts/Right',
                '@id' => '/api/v0/rights',
                '@type' => 'hydra:Collection',
                // We are logged in, so we can see self properties
                'hydra:member' => [
                    [
                        'user' => $this->findIriBy(User::class, ['email' => TestsFixtures::USER_EMAIL]),
                    ],
                ],
                'hydra:totalItems' => 1,
            ],
            Right::class
        );
    }

    /**
     * @covers \App\Security\RightExtension::__construct()
     * @covers \App\Security\RightExtension::applyToCollection()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testGetCollectionAnonymous(): void
    {
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        static::createClient()->request('GET', '/api/v0/rights');
        static::assertResponseIsForbidden();
    }
}
