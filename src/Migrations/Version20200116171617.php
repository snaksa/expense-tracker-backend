<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200116171617 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create Transaction table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('transaction');

        $table->addColumn('id', 'integer')
            ->setAutoincrement(true)
            ->setUnsigned(true)
            ->setNotnull(true);

        $table->addColumn('description', 'string')
            ->setNotnull(true)
            ->setLength(255);

        $table->addColumn('value', 'float')
            ->setNotnull(true);

        $table->addColumn('type', 'integer')
            ->setNotnull(true);

        $table->addColumn('date', 'datetime')
            ->setNotnull(true);

        $table->addColumn('wallet_id', 'integer')
            ->setNotnull(true)
            ->setUnsigned(true);

        $table->addColumn('category_id', 'integer')
            ->setNotnull(true)
            ->setUnsigned(true);

        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint(
            'wallet',
            ['wallet_id'],
            ['id'],
            ['onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'],
            'fk_transaction_wallet'
        );
        $table->addForeignKeyConstraint(
            'category',
            ['category_id'],
            ['id'],
            ['onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'],
            'fk_transaction_category'
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('transaction');
    }
}
