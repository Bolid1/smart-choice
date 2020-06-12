<?php

declare(strict_types=1);

namespace App\Tests\Controller\Company;

use App\Entity\Company;
use App\Test\ApiTestCase;

class UsersControllerTest extends ApiTestCase
{
    /**
     * @covers \App\Controller\Company\UsersController::list()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testAsAdmin(): void
    {
        $client = static::createCompanyAdminClient();

        $company = $this->findItemBy(Company::class, ['name' => 'Richards family'])->getId();
        $client->request('GET', "/company/{$company}/users/");

        static::assertResponseStatusCodeSame(200);
    }

    /**
     * @covers \App\Controller\Company\UsersController::list()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testAsUser(): void
    {
        $client = static::createAuthenticatedClient();

        $company = $this->findItemBy(Company::class, ['name' => 'Richards family'])->getId();
        $client->request('GET', "/company/{$company}/users/");

        static::assertResponseStatusCodeSame(403);
    }

    /**
     * @covers \App\Controller\Company\UsersController::list()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testAnonymous(): void
    {
        $client = static::createClient();

        $company = $this->findItemBy(Company::class, ['name' => 'Richards family'])->getId();
        $client->request('GET', "/company/{$company}/users/");

        static::assertResponseStatusCodeSame(403);
    }
}
