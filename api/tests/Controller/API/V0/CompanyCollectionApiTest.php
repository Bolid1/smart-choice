<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\Entity\Company;
use App\Test\ApiTestCase;

class CompanyCollectionApiTest extends ApiTestCase
{
    /**
     * @covers \App\Security\CompanyExtension::__construct()
     * @covers \App\Security\CompanyExtension::applyToCollection()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testGetCompanyCollection(): void
    {
        static::createCompanyAdminClient()->request('GET', '/api/v0/companies');
        static::assertResponseIsSuccessfulCollectionJsonSchema(
            [
                '@context' => '/api/v0/contexts/Company',
                '@id' => '/api/v0/companies',
                '@type' => 'hydra:Collection',
                // We are logged in, so we can see self properties
                'hydra:member' => [
                    [
                        'name' => 'Richards family',
                    ],
                ],
                'hydra:totalItems' => 1,
            ],
            Company::class
        );
    }

    /**
     * @covers \App\Security\CompanyExtension::__construct()
     * @covers \App\Security\CompanyExtension::applyToCollection()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testGetCollectionAnonymous(): void
    {
        // The client implements Symfony HttpClient's `HttpClientInterface`, and the response `ResponseInterface`
        static::createClient()->request('GET', '/api/v0/companies');
        static::assertResponseIsForbidden();
    }
}
