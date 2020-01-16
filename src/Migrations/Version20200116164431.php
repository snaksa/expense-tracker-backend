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

        $table->addColumn('email', 'string')
            ->setNotnull(true)
            ->setLength(180);

        $table->addColumn('password', 'string')
            ->setNotnull(true);

        $table->addColumn('roles', 'json')
                ->setNotnull(true);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['email']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('user');
    }
}
