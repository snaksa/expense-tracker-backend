<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200116165853 extends AbstractMigration
{
    public function getDescription() : string
    {
        return 'Create Category table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable('category');

        $table->addColumn('id', 'integer')
            ->setAutoincrement(true)
            ->setUnsigned(true)
            ->setNotnull(true);

        $table->addColumn('name', 'string')
            ->setNotnull(true)
            ->setLength(255);

        $table->addColumn('color', 'integer')
            ->setNotnull(true);

        $table->addColumn('icon', 'integer')
            ->setNotnull(true);

        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['name']);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable('category');
    }
}
