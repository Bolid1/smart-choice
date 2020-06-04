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
final class Version20200603195739 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates invitation table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('invitation');

        $table->addColumn(
            'id',
            UuidType::NAME,
            [
                'notnull' => true,
            ]
        );

        $table->addColumn(
            'from_user_id',
            UuidType::NAME,
            [
                'notnull' => true,
            ]
        );

        $table->addColumn(
            'to_company_id',
            UuidType::NAME,
            [
                'notnull' => true,
            ]
        );

        $table->addColumn(
            'created_at',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => true,
                'comment' => 'Invitation creation date',
            ]
        );

        $table->addColumn(
            'updated_at',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => true,
                'comment' => 'Invitation was last updated at date',
            ]
        );

        $table->addColumn(
            'email',
            Types::STRING,
            [
                'notnull' => true,
                'length' => 255,
            ]
        );

        $table->addColumn(
            'secret',
            Types::STRING,
            [
                'notnull' => true,
                'length' => 255,
            ]
        );

        $table->addColumn(
            'admin',
            Types::BOOLEAN,
            [
                'default' => false,
            ]
        );

        $table
            ->setPrimaryKey(['id'], 'invitation__pk')
            ->addIndex(['from_user_id'], 'invitation__from_user_id__idx')
            ->addForeignKeyConstraint(
                'user',
                ['from_user_id'],
                ['id'],
                ['onDelete' => 'cascade'],
                'invitation__from_user_id__fk'
            )
            ->addIndex(['to_company_id'], 'invitation__to_company_id__idx')
            ->addForeignKeyConstraint(
                'company',
                ['to_company_id'],
                ['id'],
                ['onDelete' => 'cascade'],
                'invitation__to_company_id__fk'
            )
            ->addIndex(['email'], 'invitation__email__idx')
            ->addUniqueIndex(
                ['from_user_id', 'to_company_id', 'email'],
                'invitation__from_user__to_company__email__uniq'
            )
        ;
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('invitation');
    }
}
