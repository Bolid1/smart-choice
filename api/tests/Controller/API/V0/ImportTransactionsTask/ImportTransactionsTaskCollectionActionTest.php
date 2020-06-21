<?php

declare(strict_types=1);

namespace App\Tests\Controller\API\V0\ImportTransactionsTask;

use App\Entity\ImportTransactionsTask;
use App\Test\ApiTestCase;
use JsonException;

class ImportTransactionsTaskCollectionActionTest extends ApiTestCase
{
    /**
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
                  '/api/v0/import_transactions_tasks',
              )
        ;

        static::assertResponseIsSuccessfulCollectionJsonSchema(
            [
                '@context' => '/api/v0/contexts/ImportTransactionsTask',
                '@id' => '/api/v0/import_transactions_tasks',
                '@type' => 'hydra:Collection',
                'hydra:member' => [
                        [
                            '@type' => 'https://schema.org/ScheduleAction',
                            'mimeType' => 'json',
                            'errors' => null,
                            'status' => 'accepted',
                            'startTime' => null,
                            'endTime' => null,
                            'successfullyImported' => 0,
                            'failedToImport' => 0,
                        ],
                    ],
                'hydra:totalItems' => 1,
            ],
            ImportTransactionsTask::class
        );
    }
}
