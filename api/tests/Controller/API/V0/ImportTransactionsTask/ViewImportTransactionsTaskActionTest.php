<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\ImportTransactionsTask;

use App\Entity\ImportTransactionsTask;
use App\Test\ApiTestCase;
use JsonException;

class ViewImportTransactionsTaskActionTest extends ApiTestCase
{
    /**
     * @covers \App\Security\ImportTransactionsTaskVoter::supports()
     * @covers \App\Security\ImportTransactionsTaskVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws JsonException
     */
    public function testViewImportTransactionsTask(): void
    {
        static::createAuthenticatedClient()
              ->request(
                  'GET',
                  $this->findIriBy(
                      ImportTransactionsTask::class,
                      ['company' => $this->findCompanyIdBy('Richards family')]
                  ),
              )
        ;

        static::assertResponseIsSuccessfulItemJsonSchema(
            [
                '@context' => '/api/v0/contexts/ImportTransactionsTask',
                '@type' => 'https://schema.org/ScheduleAction',

                'status' => 'accepted',
                'startTime' => null,
                'endTime' => null,
                'successfullyImported' => 0,
                'failedToImport' => 0,
                'errors' => null,
            ],
            ImportTransactionsTask::class
        );
    }

    /**
     * @covers \App\Security\ImportTransactionsTaskVoter::supports()
     * @covers \App\Security\ImportTransactionsTaskVoter::voteOnAttribute()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws JsonException
     */
    public function testViewImportTransactionsTaskInAnotherAccount(): void
    {
        static::createAuthenticatedClient()
              ->request(
                  'GET',
                  $this->findIriBy(
                      ImportTransactionsTask::class,
                      ['company' => $this->findCompanyIdBy('Corporation LTD')]
                  ),
              )
        ;

        static::assertResponseIsForbidden();
    }
}
