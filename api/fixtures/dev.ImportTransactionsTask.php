<?php

declare(strict_types=1);

use App\Entity\ImportTransactionsTask;

/* @noinspection PhpUnhandledExceptionInspection */
return [
    ImportTransactionsTask::class => [
        'my_import_transactions_task' => [
            'company' => '@my_company',
            'user' => '@my_admin',
            'data' => \json_encode(
                [
                    'account' => 'Salary card',
                    'date' => '2020-06-21 21:38',
                ],
                JSON_THROW_ON_ERROR
            ),
            'mimeType' => 'json',
            'scheduledTime' => '<dateTimeBetween("now", "+10 days")>',
        ],
        'my_import_transactions_task_in_past' => [
            'company' => '@my_company',
            'user' => '@my_admin',
            'data' => \implode(
                "\n",
                \array_map(
                    static function (array $item) {
                        return \implode(
                            ',',
                            \array_map(
                                static function ($data) {
                                    return "\"{$data}\"";
                                },
                                $item
                            )
                        );
                    },
                    [
                        ['account', 'date'],
                        ['Salary card', '2020-06-21 21:38'],
                    ],
                )
            ),
            'mimeType' => 'csv',
            'scheduledTime' => '<dateTimeBetween("-10 days", "now")>',
        ],
        'my_import_transactions_task_started' => [
            'company' => '@my_company',
            'user' => '@my_admin',
            'data' => \json_encode(
                [
                    'account' => 'Salary card',
                    'date' => '2020-06-21 21:38',
                ],
                JSON_THROW_ON_ERROR
            ),
            'mimeType' => 'json',
            'scheduledTime' => '<dateTimeBetween("-10 days", "-1 hour")>',
            'status' => ImportTransactionsTask::STATUS_STARTED,
            'startTime' => '<dateTimeBetween("-1 hour", "-30 minutes")>',
            'successfullyImported' => 12,
            'failedToImport' => 2,
        ],
        'my_import_transactions_task_finished' => [
            'company' => '@my_company',
            'user' => '@my_admin',
            'data' => \json_encode(
                [
                    'account' => 'Salary card',
                    'date' => '2020-06-21 21:38',
                ],
                JSON_THROW_ON_ERROR
            ),
            'mimeType' => 'json',
            'scheduledTime' => '<dateTimeBetween("-10 days", "-1 hour")>',
            'status' => ImportTransactionsTask::STATUS_FINISHED,
            'startTime' => '<dateTimeBetween("-1 hour", "-30 minutes")>',
            'endTime' => '<dateTimeBetween("-20 minutes", "-10 minutes")>',
            'successfullyImported' => 65,
            'failedToImport' => 74,
            'errors' => [
                1 => 'Account not found',
                2 => 'Account not found',
                45 => 'Invalid date',
                46 => 'Account not found',
                85 => 'Account not found',
            ],
        ],
    ],
];
