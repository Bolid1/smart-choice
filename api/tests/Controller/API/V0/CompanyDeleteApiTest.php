<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

use App\DataFixtures\TestsFixtures;
use App\Entity\Company;
use App\Test\ApiTestCase;

class CompanyDeleteApiTest extends ApiTestCase
{
    /**
     * @covers \App\Security\CompanyVoter::supports()
     * @covers \App\Security\CompanyVoter::voteOnAttribute()
     * @covers \App\DataPersister\CompanyDataPersister::__construct()
     * @covers \App\DataPersister\CompanyDataPersister::supports()
     * @covers \App\DataPersister\CompanyDataPersister::remove()
     *
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
     * @covers \App\Security\CompanyVoter::supports()
     * @covers \App\Security\CompanyVoter::voteOnAttribute()
     *
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
     * @covers \App\Security\CompanyVoter::supports()
     * @covers \App\Security\CompanyVoter::voteOnAttribute()
     *
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
     * @covers \App\Security\CompanyVoter::supports()
     * @covers \App\Security\CompanyVoter::voteOnAttribute()
     *
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
