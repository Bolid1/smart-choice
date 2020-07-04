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
final class Version20200701182517 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates category table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('category');

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
            'parent_id',
            UuidType::NAME,
            [
                'notnull' => false,
                // 'comment' => 'Parent category',
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

        $table->addColumn('lft', Types::INTEGER, ['notnull' => true]);
        $table->addColumn('rgt', Types::INTEGER, ['notnull' => true]);
        $table->addColumn('level', Types::INTEGER, ['notnull' => true]);

        $table->addColumn(
            'created_at',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => true,
                'comment' => 'Category creation date',
            ]
        );

        $table->addColumn(
            'updated_at',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => true,
                'comment' => 'Category was last updated at date',
            ]
        );

        $table
            ->addIndex(['parent_id'], 'category__parent_id__idx')
            ->addForeignKeyConstraint(
                'category',
                ['parent_id'],
                ['id'],
                ['onDelete' => 'set null'],
                'category__parent_id__fk'
            )
            ->addIndex(['company_id'], 'category__company_id__idx')
            ->addForeignKeyConstraint(
                'company',
                ['company_id'],
                ['id'],
                ['onDelete' => 'cascade'],
                'category__company_id__fk'
            )
            ->setPrimaryKey(['id'], 'category__pk')
        ;
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('category');
    }
}
