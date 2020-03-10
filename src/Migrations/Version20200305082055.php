<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200305082055 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->getTable('user');

        $table->addColumn('external_id', 'string')
            ->setLength(255)
            ->setNotnull(false);

        $table->addUniqueIndex(['external_id']);
    }

    public function down(Schema $schema) : void
    {
        $table = $schema->getTable('user');

        $table->dropColumn('external_id');
    }
}
