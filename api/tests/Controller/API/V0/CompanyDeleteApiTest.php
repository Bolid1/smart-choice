<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\DataFixtures\TestsFixtures;
use App\Entity\Company;
use App\Test\ApiTestCase;

class CompanyDeleteApiTest extends ApiTestCase
{
    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDeleteCompany(): void
    {
        static::createAnotherAdminClient()->request(
            'DELETE',
            $this->findIriBy(Company::class, ['name' => TestsFixtures::ANOTHER_COMPANY_NAME]),
        )
        ;

        static::assertResponseIsSuccessful();
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDeleteCompanyAnonymous(): void
    {
        static::createClient()->request(
            'DELETE',
            $this->findIriBy(Company::class, ['name' => TestsFixtures::ANOTHER_COMPANY_NAME]),
        )
        ;

        static::assertResponseIsForbidden();
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDeleteCompanyAsUser(): void
    {
        static::createAuthenticatedClient()->request(
            'DELETE',
            $this->findIriBy(Company::class, ['name' => TestsFixtures::COMPANY_NAME]),
        )
        ;

        static::assertResponseIsForbidden();
    }

    /**
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDeleteCompanyWithManyUsers(): void
    {
        static::createCompanyAdminClient()->request(
            'DELETE',
            $this->findIriBy(Company::class, ['name' => TestsFixtures::COMPANY_NAME]),
        )
        ;

        static::assertResponseIsForbidden();
    }
}
