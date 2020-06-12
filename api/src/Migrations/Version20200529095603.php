<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20200529095603 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates company table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('company');

        $table->addColumn(
            'id',
            UuidType::NAME,
            [
                'notnull' => true,
            ]
        );
        $table->setPrimaryKey(['id'], 'company__pk');

        $table->addColumn(
            'name',
            Types::STRING,
            [
                'length' => 255,
                'notnull' => false,
            ]
        );

        $table->addColumn(
            'created_at',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => true,
                'comment' => 'Company creation date',
            ]
        );

        $table->addColumn(
            'updated_at',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => true,
                'comment' => 'Company was last updated at date',
            ]
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('company');
    }
}
