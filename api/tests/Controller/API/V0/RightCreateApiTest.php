<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\DataFixtures\TestsFixtures;
use App\Entity\Company;
use App\Entity\Right;
use App\Entity\User;
use App\Test\ApiTestCase;

class RightCreateApiTest extends ApiTestCase
{
    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateRight(): void
    {
        // Admin can add any registered user to company
        static::createCompanyAdminClient()->request(
            'POST',
            '/api/v0/rights',
            [
                'json' => $data = [
                    'user' => $this->findIriBy(User::class, ['email' => TestsFixtures::ANOTHER_ADMIN_EMAIL]),
                    'company' => $this->findIriBy(Company::class, ['name' => TestsFixtures::COMPANY_NAME]),
                    'admin' => true,
                ],
            ]
        )
        ;

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Right',
                '@type' => 'https://schema.org/Role',
            ] + $data,
            Right::class
        );
    }
}
