<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\ImportTransactionsTask;

use App\Entity\ImportTransactionsTask;
use App\Test\ApiTestCase;
use DateTimeInterface;
use JsonException;

class CreateImportTransactionsTaskActionTest extends ApiTestCase
{
    /**
     * @covers \App\Security\ImportTransactionsTaskVoter::supports()
     * @covers \App\Security\ImportTransactionsTaskVoter::voteOnAttribute()
     * @covers \App\DataPersister\ImportTransactionsTaskDataPersister::__construct()
     * @covers \App\DataPersister\ImportTransactionsTaskDataPersister::supports()
     * @covers \App\DataPersister\ImportTransactionsTaskDataPersister::persist()
     *
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface
     * @throws JsonException
     */
    public function testCreateImportTransactionsTask(): void
    {
        static::createAuthenticatedClient()
              ->request(
                  'POST',
                  '/api/v0/import_transactions_tasks',
                  [
                      'json' => $json = [
                          'company' => $company = $this->findCompanyIriBy($companyName = 'Richards family'),
                          'data' => \json_encode(
                              [
                                  [
                                      'account' => 'Salary card',
                                      'date' => '2020-12-03',
                                      'amount' => '123.23',
                                  ],
                                  [
                                      'account' => 'Salary card',
                                      'date' => null,
                                      'amount' => '654.12',
                                  ],
                                  [
                                      'account' => 'Empty account',
                                      'amount' => '985.65',
                                  ],
                              ],
                              JSON_THROW_ON_ERROR
                          ),
                          'mimeType' => 'json',
                      ],
                  ]
              )
        ;

        static::assertResponseIsSuccessfulItemJsonSchema(
            $json + [
                '@context' => '/api/v0/contexts/ImportTransactionsTask',
                '@type' => 'https://schema.org/ScheduleAction',

                'status' => 'accepted',
                'startTime' => null,
                'endTime' => null,
                'successfullyImported' => 0,
                'failedToImport' => 0,
                'errors' => null,
                'user' => $this->getIriFromItem($this->findUserByEmail('user@doctrine.fixture')),
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
    public function testCreateImportTransactionsTaskInAnotherAccount(): void
    {
        static::createAuthenticatedClient()
              ->request(
                  'POST',
                  '/api/v0/import_transactions_tasks',
                  [
                      'json' => $json = [
                          'company' => $company = $this->findCompanyIriBy($companyName = 'Corporation LTD'),
                          'data' => \json_encode(
                              [
                                  [
                                      'account' => 'Salary card',
                                      'date' => '2020-12-03',
                                      'amount' => '123.23',
                                  ],
                                  [
                                      'account' => 'Salary card',
                                      'date' => null,
                                      'amount' => '654.12',
                                  ],
                                  [
                                      'account' => 'Empty account',
                                      'amount' => '985.65',
                                  ],
                              ],
                              JSON_THROW_ON_ERROR
                          ),
                          'mimeType' => 'json',
                          'scheduledTime' => \date(DateTimeInterface::RFC3339),
                      ],
                  ]
              )
        ;

        static::assertResponseIsForbidden();
    }
}
