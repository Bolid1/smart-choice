<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200621144234 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates import_transactions_task table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('import_transactions_task');

        $table->addColumn(
            'id',
            UuidType::NAME,
            [
                'notnull' => true,
            ]
        );

        $table->addColumn(
            'company_id',
            UuidType::NAME,
            [
                'notnull' => true,
                // 'comment' => 'To which company should transactions be added',
            ]
        );

        $table->addColumn(
            'user_id',
            UuidType::NAME,
            [
                'notnull' => true,
                // 'comment' => 'From which user should transactions be added',
            ]
        );

        $table->addColumn(
            'data',
            Types::TEXT,
            [
                'notnull' => true,
                'comment' => 'Data to import',
            ]
        );

        $table->addColumn(
            'mime_type',
            Types::STRING,
            [
                'length' => 10,
                'notnull' => true,
                'comment' => 'Mime type of data',
            ]
        );

        $table->addColumn(
            'errors',
            Types::JSON,
            [
                'notnull' => false,
                'comment' => 'Errors, that occurred during task process',
            ]
        );

        $table->addColumn(
            'created_at',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => true,
                'comment' => 'Task creation date',
            ]
        );

        $table->addColumn(
            'updated_at',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => true,
                'comment' => 'Task was last updated at date',
            ]
        );

        $table->addColumn(
            'scheduled_time',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => true,
                'comment' => 'Task process should be started at',
            ]
        );

        $table->addColumn(
            'status',
            Types::STRING,
            [
                'length' => 15,
                'default' => 'accepted',
                'notnull' => true,
                'comment' => 'Task status',
            ]
        );

        $table->addColumn(
            'start_time',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => false,
                'comment' => 'Task process started at',
            ]
        );

        $table->addColumn(
            'end_time',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => false,
                'comment' => 'Task process finished at',
            ]
        );

        $table->addColumn(
            'successfully_imported',
            Types::INTEGER,
            [
                'default' => 0,
                'notnull' => true,
                'comment' => 'Count of transactions, that was successfully imported',
            ]
        );

        $table->addColumn(
            'failed_to_import',
            Types::INTEGER,
            [
                'default' => 0,
                'notnull' => true,
                'comment' => 'Count of transactions, that was rejected',
            ]
        );

        $table
            ->addIndex(['user_id'], 'import_transactions_task__user_id__idx')
            ->addForeignKeyConstraint(
                'user',
                ['user_id'],
                ['id'],
                ['onDelete' => 'cascade'],
                'import_transactions_task__user_id__fk'
            )
            ->addIndex(['company_id'], 'import_transactions_task__company_id__idx')
            ->addForeignKeyConstraint(
                'company',
                ['company_id'],
                ['id'],
                ['onDelete' => 'cascade'],
                'import_transactions_task__company_id__fk'
            )
            ->setPrimaryKey(['id'], 'import_transactions_task__pk')
        ;
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('import_transactions_task');
    }
}
