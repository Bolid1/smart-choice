<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\Entity\Company;
use App\Entity\Invitation;
use App\Entity\User;
use App\Test\ApiTestCase;

class InvitationCreateApiTest extends ApiTestCase
{
    /**
     * @covers \App\Security\InvitationVoter::__construct()
     * @covers \App\Security\InvitationVoter::supports()
     * @covers \App\Security\InvitationVoter::voteOnAttribute()
     * @covers \App\DataPersister\InvitationDataPersister::__construct()
     * @covers \App\DataPersister\InvitationDataPersister::supports()
     * @covers \App\DataPersister\InvitationDataPersister::persist()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateInvitation(): void
    {
        // Admin can add any registered user to company
        static::createCompanyAdminClient()->request(
            'POST',
            '/api/v0/invitations',
            [
                'json' => $data = [
                    'toCompany' => $company = $this->findIriBy(
                        Company::class,
                        ['name' => 'Richards family']
                    ),
                    'email' => $email = 'invited@user.test',
                    'plainSecret' => 'Super secret',
                    'admin' => true,
                ],
            ]
        )
        ;

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Invitation',
                '@type' => 'Invitation',
                'fromUser' => $this->findIriBy(
                    User::class,
                    ['email' => 'admin@doctrine.fixture']
                ),
                'toCompany' => $company,
                'email' => $email,
                'admin' => true,
            ],
            Invitation::class
        );
    }

    /**
     * @covers \App\Security\InvitationVoter::__construct()
     * @covers \App\Security\InvitationVoter::supports()
     * @covers \App\Security\InvitationVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateInvitationAsUser(): void
    {
        // Admin can add any registered user to company
        static::createAuthenticatedClient()->request(
            'POST',
            '/api/v0/invitations',
            [
                'json' => $data = [
                    'toCompany' => $company = $this->findIriBy(
                        Company::class,
                        ['name' => 'Richards family']
                    ),
                    'email' => 'invited@user.test',
                    'plainSecret' => 'Super secret',
                    'admin' => true,
                ],
            ]
        )
        ;

        static::assertResponseIsForbidden();
    }

    /**
     * @covers \App\Security\InvitationVoter::__construct()
     * @covers \App\Security\InvitationVoter::supports()
     * @covers \App\Security\InvitationVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateInvitationToAnotherCompany(): void
    {
        // Admin can add any registered user to company
        static::createCompanyAdminClient()->request(
            'POST',
            '/api/v0/invitations',
            [
                'json' => $data = [
                    'toCompany' => $company = $this->findIriBy(
                        Company::class,
                        ['name' => 'Corporation LTD']
                    ),
                    'email' => 'invited@user.test',
                    'plainSecret' => 'Super secret',
                    'admin' => true,
                ],
            ]
        )
        ;

        static::assertResponseIsForbidden();
    }

    /**
     * @covers \App\Security\InvitationVoter::__construct()
     * @covers \App\Security\InvitationVoter::supports()
     * @covers \App\Security\InvitationVoter::voteOnAttribute()
     *
     * @dataProvider invalidDataProvider
     *
     * @param string|null $company
     * @param array $rest
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateInvalidInvitation(?string $company, array $rest): void
    {
        // Admin can add any registered user to company
        static::createAnotherAdminClient()->request(
            'POST',
            '/api/v0/invitations',
            [
                'json' => [
                              'toCompany' => $company
                                  ? $this->findIriBy(
                                      Company::class,
                                      ['name' => $company]
                                  )
                                  : null,
                          ] + $rest,
            ]
        )
        ;

        static::assertResponseIsInvalidParams();
    }

    public function invalidDataProvider(): array
    {
        $validData = [
            'email' => 'user@doctrine.fixture',
            'plainSecret' => 'Super secret',
            'admin' => true,
        ];

        return [
            'email is null' => ['Corporation LTD', ['email' => null] + $validData],
            'email is empty string' => ['Corporation LTD', ['email' => ''] + $validData],
            'email is invalid' => ['Corporation LTD', ['email' => 'test.com'] + $validData],
            'plainSecret is invalid' => ['Corporation LTD', ['plainSecret' => 'test'] + $validData],
            'plainSecret is empty' => ['Corporation LTD', ['plainSecret' => ''] + $validData],
            'the company is empty' => [null, $validData],
        ];
    }
}
