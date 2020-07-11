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
final class Version20200711184909 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creates transaction_category table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('transaction_category');

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
            'category_id',
            UuidType::NAME,
            [
                'notnull' => true,
                // 'comment' => 'To which company should transactions be added',
            ]
        );

        $table->addColumn(
            'transaction_id',
            UuidType::NAME,
            [
                'notnull' => true,
                // 'comment' => 'To which company should transactions be added',
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
            ]
        );

        $table->addColumn(
            'updated_at',
            Types::DATETIMETZ_IMMUTABLE,
            [
                'notnull' => true,
            ]
        );

        $table
            ->addIndex(['company_id'], 'transaction_category__company_id__idx')
            ->addForeignKeyConstraint(
                'company',
                ['company_id'],
                ['id'],
                ['onDelete' => 'cascade'],
                'transaction_category__company_id__fk'
            )
            ->addIndex(['category_id'], 'transaction_category__category_id__idx')
            ->addForeignKeyConstraint(
                'category',
                ['category_id'],
                ['id'],
                ['onDelete' => 'cascade'],
                'transaction_category__category_id__fk'
            )
            ->addIndex(['transaction_id'], 'transaction_category__transaction_id__idx')
            ->addForeignKeyConstraint(
                'transaction',
                ['transaction_id'],
                ['id'],
                ['onDelete' => 'cascade'],
                'transaction_category__transaction_id__fk'
            )
            ->setPrimaryKey(['id'], 'transaction_category__pk')
        ;
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('transaction_category');
    }
}
