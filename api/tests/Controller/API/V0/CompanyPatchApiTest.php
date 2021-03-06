<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\Entity\Company;
use App\Test\ApiTestCase;

class CompanyPatchApiTest extends ApiTestCase
{
    /**
     * @covers \App\Security\CompanyVoter::supports()
     * @covers \App\Security\CompanyVoter::voteOnAttribute()
     * @covers \App\DataPersister\CompanyDataPersister::__construct()
     * @covers \App\DataPersister\CompanyDataPersister::supports()
     * @covers \App\DataPersister\CompanyDataPersister::persist()
     *
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
            $this->findIriBy(Company::class, ['name' => 'Richards family']),
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
     * @covers \App\Security\CompanyVoter::supports()
     * @covers \App\Security\CompanyVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testPatchCompanyByUser(): void
    {
        static::createAuthenticatedClient()->request(
            'PATCH',
            $this->findIriBy(Company::class, ['name' => 'Richards family']),
            [
                'json' => [
                    'name' => $name = 'New company name',
                ],
            ]
        );

        static::assertResponseIsForbidden();
    }
}
