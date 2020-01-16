<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200116170121 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create Wallet table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('wallet');

        $table->addColumn('id', 'integer')
            ->setAutoincrement(true)
            ->setUnsigned(true)
            ->setNotnull(true);

        $table->addColumn('name', 'string')
            ->setNotnull(true)
            ->setLength(255);

        $table->addColumn('color', 'integer')
            ->setNotnull(true);

        $table->addColumn('user_id', 'integer')
            ->setNotnull(true)
            ->setUnsigned(true);

        $table->setPrimaryKey(['id']);
        $table->addForeignKeyConstraint(
            'user',
            ['user_id'],
            ['id'],
            ['onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'],
            'fk_wallet_user'
        );
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('wallet');
    }
}
