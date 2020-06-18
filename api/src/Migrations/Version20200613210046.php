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
final class Version20200613210046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates transaction table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('transaction');

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
            ]
        );

        $table->addColumn(
            'account_id',
            UuidType::NAME,
            [
                'notnull' => true,
            ]
        );

        $table->addColumn(
            'date',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => false,
            ]
        );

        $table->addColumn(
            'amount',
            Types::FLOAT,
            [
                'notnull' => true,
            ]
        );

        $table->addColumn(
            'created_at',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => true,
                'comment' => 'Transaction creation date',
            ]
        );

        $table->addColumn(
            'updated_at',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => true,
                'comment' => 'Transaction was last updated at date',
            ]
        );

        $table
            ->addIndex(['account_id'], 'transaction__account_id__idx')
            ->addForeignKeyConstraint(
                'account',
                ['account_id'],
                ['id'],
                [],
                'transaction__account_id__fk'
            )
            ->addIndex(['company_id'], 'transaction__company_id__idx')
            ->addForeignKeyConstraint(
                'company',
                ['company_id'],
                ['id'],
                ['onDelete' => 'cascade'],
                'transaction__company_id__fk'
            )
            ->setPrimaryKey(['id'], 'transaction__pk')
        ;
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('transaction');
    }
}
