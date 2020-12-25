<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201225125227 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('budget');

        $table->addColumn('id', 'integer')
            ->setAutoincrement(true)
            ->setUnsigned(true)
            ->setNotnull(true);

        $table->addColumn('name', 'string')
            ->setNotnull(true)
            ->setLength(255);

        $table->addColumn('value', 'float')
            ->setNotnull(true);

        $table->addColumn('user_id', 'integer')
            ->setNotnull(false)
            ->setUnsigned(true);

        $table->addColumn('start_date', 'datetime')
            ->setNotnull(true);

        $table->addColumn('end_date', 'datetime')
            ->setNotnull(true);

        $table->setPrimaryKey(['id']);

        $table->addForeignKeyConstraint(
            'user',
            ['user_id'],
            ['id'],
            ['onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'],
            'fk_budget_user'
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('budget');
    }
}
