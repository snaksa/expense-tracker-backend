<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201004152311 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('transaction_label');

        $table->addColumn('transaction_id', 'integer')
            ->setUnsigned(true)
            ->setNotnull(true);

        $table->addColumn('label_id', 'integer')
            ->setUnsigned(true)
            ->setNotnull(true);

        $table->setPrimaryKey(['transaction_id', 'label_id']);
        $table->addForeignKeyConstraint(
            'transaction_record',
            ['transaction_id'],
            ['id'],
            ['onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'],
            'fk_transaction_label_transaction_record'
        );
        $table->addForeignKeyConstraint(
            'label',
            ['label_id'],
            ['id'],
            ['onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'],
            'fk_transaction_label_label'
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('transaction_label');
    }
}
