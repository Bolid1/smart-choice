<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;
use Ramsey\Uuid\Doctrine\UuidType;

final class Version20200529100124 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates right table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('right');

        $table->addColumn(
            'user_id',
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

        $table
            ->addIndex(['user_id'], 'right__user_id__idx')
            ->addForeignKeyConstraint(
                'user',
                ['user_id'],
                ['id'],
                ['onDelete' => 'cascade'],
                'right__user_id__fk'
            )
            ->addIndex(['company_id'], 'right__company_id__idx')
            ->addForeignKeyConstraint(
                'company',
                ['company_id'],
                ['id'],
                ['onDelete' => 'cascade'],
                'right__company_id__fk'
            )
            ->setPrimaryKey(['user_id', 'company_id'], 'right__pk')
        ;

        $table->addColumn(
            'admin',
            Types::BOOLEAN,
            [
                'default' => false,
                'comment' => 'Is user admin in company?',
            ]
        );

        $table->addColumn(
            'created_at',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => true,
                'comment' => 'Right creation date',
            ]
        );

        $table->addColumn(
            'updated_at',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => true,
                'comment' => 'Right was last updated at date',
            ]
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('right');
    }
}
