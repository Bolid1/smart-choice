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
final class Version20200613145550 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates account table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('account');

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
            'currency',
            Types::STRING,
            [
                'length' => 3,
                'notnull' => true,
            ]
        );

        $table->addColumn(
            'name',
            Types::STRING,
            [
                'length' => 255,
                'notnull' => true,
            ]
        );

        $table->addColumn(
            'balance',
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
                'comment' => 'Account creation date',
            ]
        );

        $table->addColumn(
            'updated_at',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => true,
                'comment' => 'Account was last updated at date',
            ]
        );

        $table
            ->addIndex(['company_id'], 'account__company_id__idx')
            ->addForeignKeyConstraint(
                'company',
                ['company_id'],
                ['id'],
                ['onDelete' => 'cascade'],
                'account__company_id__fk'
            )
            ->setPrimaryKey(['id'], 'account__pk')
        ;
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('account');
    }
}
