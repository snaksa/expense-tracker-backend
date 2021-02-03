<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201225125420 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('budget_label');

        $table->addColumn('budget_id', 'integer')
            ->setUnsigned(true)
            ->setNotnull(true);

        $table->addColumn('label_id', 'integer')
            ->setUnsigned(true)
            ->setNotnull(true);

        $table->setPrimaryKey(['budget_id', 'label_id']);
        $table->addForeignKeyConstraint(
            'budget',
            ['budget_id'],
            ['id'],
            ['onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'],
            'fk_budget_label_budget'
        );
        $table->addForeignKeyConstraint(
            'label',
            ['label_id'],
            ['id'],
            ['onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'],
            'fk_budget_label_label'
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('budget_label');
    }
}
