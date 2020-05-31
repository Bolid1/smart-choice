<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\DataFixtures\TestsFixtures;
use App\Entity\Company;
use App\Test\ApiTestCase;

class CompanyViewControllerTest extends ApiTestCase
{
    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewCompany(): void
    {
        static::createCompanyAdminClient()->request(
            'GET',
            $this->findIriBy(Company::class, ['name' => TestsFixtures::COMPANY_NAME])
        )
        ;

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/Company',
                '@type' => 'https://schema.org/Organization',
                'name' => TestsFixtures::COMPANY_NAME,
            ],
            Company::class
        );
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testViewCompanyAnonymous(): void
    {
        static::createClient()->request(
            'GET',
            $this->findIriBy(Company::class, ['name' => TestsFixtures::COMPANY_NAME])
        )
        ;

        static::assertResponseIsForbidden();
    }
}