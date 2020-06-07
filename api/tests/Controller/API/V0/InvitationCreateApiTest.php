<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\DataFixtures\TestsFixtures;
use App\Entity\Company;
use App\Entity\Invitation;
use App\Entity\User;
use App\Test\ApiTestCase;

class InvitationCreateApiTest extends ApiTestCase
{
    /**
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
                        ['name' => TestsFixtures::COMPANY_NAME]
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
                    ['email' => TestsFixtures::ADMIN_EMAIL]
                ),
                'toCompany' => $company,
                'email' => $email,
                'admin' => true,
            ],
            Invitation::class
        );
    }

    /**
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
                        ['name' => TestsFixtures::COMPANY_NAME]
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
                        ['name' => TestsFixtures::ANOTHER_COMPANY_NAME]
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
            'email' => TestsFixtures::USER_EMAIL,
            'plainSecret' => 'Super secret',
            'admin' => true,
        ];

        return [
            'email is null' => [TestsFixtures::ANOTHER_COMPANY_NAME, ['email' => null] + $validData],
            'email is empty string' => [TestsFixtures::ANOTHER_COMPANY_NAME, ['email' => ''] + $validData],
            'email is invalid' => [TestsFixtures::ANOTHER_COMPANY_NAME, ['email' => 'test.com'] + $validData],
            'plainSecret is invalid' => [TestsFixtures::ANOTHER_COMPANY_NAME, ['plainSecret' => 'test'] + $validData],
            'plainSecret is empty' => [TestsFixtures::ANOTHER_COMPANY_NAME, ['plainSecret' => ''] + $validData],
            'the company is empty' => [null, $validData],
        ];
    }
}
