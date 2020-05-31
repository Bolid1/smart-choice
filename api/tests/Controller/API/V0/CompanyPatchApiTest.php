<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\DataFixtures\TestsFixtures;
use App\Entity\Company;
use App\Test\ApiTestCase;

class CompanyPatchApiTest extends ApiTestCase
{
    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchCompany(): void
    {
        static::createCompanyAdminClient()->request(
            'PATCH',
            $this->findIriBy(Company::class, ['name' => TestsFixtures::COMPANY_NAME]),
            [
                'json' => [
                    'name' => $name = 'New company name',
                ],
            ]
        );

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Company',
                '@type' => 'https://schema.org/Organization',
                'name' => $name,
            ],
            Company::class
        );
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchCompanyByUser(): void
    {
        static::createAuthenticatedClient()->request(
            'PATCH',
            $this->findIriBy(Company::class, ['name' => TestsFixtures::COMPANY_NAME]),
            [
                'json' => [
                    'name' => $name = 'New company name',
                ],
            ]
        );

        static::assertResponseIsForbidden();
    }
}
