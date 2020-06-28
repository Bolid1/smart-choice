<?php

declare(strict_types=1);

namespace App\Tests\Controller\Company;

use App\Entity\ImportTransactionsTask;
use App\Test\ApiTestCase;

class ImportTransactionsTaskControllerTest extends ApiTestCase
{
    /**
     * @covers \App\Controller\Company\ImportTransactionsTaskController::list
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testList(): void
    {
        static::createAuthenticatedClient()
              ->request(
                  'GET',
                  \sprintf('/company/%s/import/transactions', $this->findCompanyIdBy('Richards family')),
              )
        ;

        static::assertResponseIsSuccessful();
    }

    /**
     * @covers \App\Controller\Company\ImportTransactionsTaskController::start
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testStart(): void
    {
        static::createAuthenticatedClient()
              ->request(
                  'PATCH',
                  \sprintf(
                      '/company/%s/import/transaction/%s',
                      $this->findCompanyIdBy('Richards family'),
                      $this->findItemBy(
                          ImportTransactionsTask::class,
                          ['company' => $this->findCompanyIdBy('Richards family')]
                      )->getId()
                  ),
              )
        ;

        static::assertResponseRedirects(null, 302);
    }

    /**
     * @covers \App\Controller\Company\ImportTransactionsTaskController::new
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testNew(): void
    {
        static::createAuthenticatedClient()
              ->request(
                  'GET',
                  \sprintf('/company/%s/import/transaction/new', $this->findCompanyIdBy('Richards family')),
              )
        ;

        static::assertResponseIsSuccessful();
    }

    /**
     * @covers \App\Controller\Company\ImportTransactionsTaskController::delete
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testDelete(): void
    {
        static::createAuthenticatedClient()
              ->request(
                  'DELETE',
                  \sprintf(
                      '/company/%s/import/transaction/%s',
                      $this->findCompanyIdBy('Richards family'),
                      $this->findItemBy(
                          ImportTransactionsTask::class,
                          ['company' => $this->findCompanyIdBy('Richards family')]
                      )->getId()
                  ),
              )
        ;

        static::assertResponseRedirects(null, 302);
    }
}
