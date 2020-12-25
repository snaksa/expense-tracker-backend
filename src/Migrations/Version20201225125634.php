<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201225125634 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('budget_category');

        $table->addColumn('budget_id', 'integer')
            ->setUnsigned(true)
            ->setNotnull(true);

        $table->addColumn('category_id', 'integer')
            ->setUnsigned(true)
            ->setNotnull(true);

        $table->setPrimaryKey(['budget_id', 'category_id']);
        $table->addForeignKeyConstraint(
            'budget',
            ['budget_id'],
            ['id'],
            ['onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'],
            'fk_budget_category_budget'
        );
        $table->addForeignKeyConstraint(
            'category',
            ['category_id'],
            ['id'],
            ['onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'],
            'fk_budget_category_category'
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('budget_category');
    }
}
