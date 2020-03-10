<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\Types;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200305122012 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('user');

        $table->dropColumn('api_key');
        $table->addColumn('api_key', 'text')->setNotnull(false);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('user');

        $table->dropColumn('api_key');
        $table->addColumn('api_key', 'string')->setNotnull(false);
    }
}
