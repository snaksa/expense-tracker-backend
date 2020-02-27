<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200116164431 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create User table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('user');

        $table->addColumn('id', 'integer')
            ->setAutoincrement(true)
            ->setUnsigned(true)
            ->setNotnull(true);

        $table->addColumn('first_name', 'string')
            ->setNotnull(false);

        $table->addColumn('last_name', 'string')
            ->setNotnull(false);

        $table->addColumn('email', 'string')
            ->setNotnull(true)
            ->setLength(180);

        $table->addColumn('password', 'string')
            ->setNotnull(true);

        $table->addColumn('api_key', 'string')
            ->setNotnull(false);

        $table->addColumn('api_key_expiry_date', 'datetime')
            ->setNotnull(false);

        $table->addColumn('roles', 'json')
                ->setNotnull(true);

        $table->addColumn('currency', 'string')
            ->setNotnull(false);

        $table->addColumn('language', 'string')
            ->setNotnull(false);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['email']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('user');
    }
}
