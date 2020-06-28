<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\ImportTransactionsTask;

use App\Entity\ImportTransactionsTask;
use App\Test\ApiTestCase;

class CancelImportTransactionsTaskActionTest extends ApiTestCase
{
    /**
     * @covers \App\Security\ImportTransactionsTaskVoter::supports()
     * @covers \App\Security\ImportTransactionsTaskVoter::voteOnAttribute()
     * @covers \App\DataPersister\ImportTransactionsTaskDataPersister::__construct()
     * @covers \App\DataPersister\ImportTransactionsTaskDataPersister::supports()
     * @covers \App\DataPersister\ImportTransactionsTaskDataPersister::remove()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCancelImportTransactionsTask(): void
    {
        static::createAuthenticatedClient()
              ->request(
                  'DELETE',
                  $this->findIriBy(
                      ImportTransactionsTask::class,
                      ['company' => $this->findCompanyIdBy('Richards family')]
                  ),
              )
        ;

        static::assertResponseIsSuccessful();
    }

    /**
     * @covers \App\Security\ImportTransactionsTaskVoter::supports()
     * @covers \App\Security\ImportTransactionsTaskVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     */
    public function testCancelImportTransactionsTaskInAnotherAccount(): void
    {
        static::createAuthenticatedClient()
              ->request(
                  'DELETE',
                  $this->findIriBy(
                      ImportTransactionsTask::class,
                      ['company' => $this->findCompanyIdBy('Corporation LTD')]
                  ),
              )
        ;

        static::assertResponseIsForbidden();
    }
}
