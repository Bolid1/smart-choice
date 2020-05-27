<?php

/** @noinspection PhpIllegalPsrClassPathInspection */

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20200524103714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates user table.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('user');

        $table->addColumn(
            'id',
            UuidType::NAME,
            [
                'notnull' => true,
            ]
        );
        $table->setPrimaryKey(['id'], 'user__pk');

        $table->addColumn(
            'email',
            Types::STRING,
            [
                'length' => 180,
                'notnull' => true,
            ]
        );
        $table->addUniqueIndex(['email'], 'user__email__uniq');

        $table->addColumn(
            'created_at',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => true,
                'comment' => 'User registration date',
            ]
        );

        $table->addColumn(
            'updated_at',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => true,
                'comment' => 'User was last updated at date',
            ]
        );

        $table->addColumn(
            'password',
            Types::STRING,
            [
                'length' => 255,
                'notnull' => true,
            ]
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('user');
    }
}
