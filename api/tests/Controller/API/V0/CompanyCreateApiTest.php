<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\Entity\Company;
use App\Test\ApiTestCase;

class CompanyCreateApiTest extends ApiTestCase
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
    public function testCreateCompany(): void
    {
        $client = static::createCompanyAdminClient();

        $response = $client
            ->request(
                'POST',
                '/api/v0/companies',
                [
                    'json' => [
                        'name' => $name = 'My awesome company',
                    ],
                ]
            )
            ->toArray(false)
        ;

        $expected = [
            '@context' => '/api/v0/contexts/Company',
            '@type' => 'https://schema.org/Organization',
            'name' => $name,
        ];
        static::assertResponseIsSuccessfulItemJsonSchema($expected, Company::class);

        $this->assertArrayHasKey('@id', $response);
        $client->request('GET', $response['@id']);
        static::assertResponseIsSuccessfulItemJsonSchema($expected, Company::class);
    }

    /**
     * @covers \App\Security\CompanyVoter::supports()
     * @covers \App\Security\CompanyVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCreateCompanyUnauthenticated(): void
    {
        static::createClient()
              ->request(
                  'POST',
                  '/api/v0/companies',
                  [
                      'json' => [
                          'name' => $name = 'My awesome company',
                      ],
                  ]
              )
        ;

        static::assertResponseIsForbidden();
    }
}
