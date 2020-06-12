<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0;

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
            $this->findIriBy(Company::class, ['name' => 'Corporation LTD']),
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
            $this->findIriBy(Company::class, ['name' => 'Corporation LTD']),
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
            $this->findIriBy(Company::class, ['name' => 'Richards family']),
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
            $this->findIriBy(Company::class, ['name' => 'Richards family']),
        )
        ;

        static::assertResponseIsForbidden();
    }
}
