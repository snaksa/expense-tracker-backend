<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200413132301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('user');

        $table->dropColumn('api_key');

        $table->dropColumn('api_key_expiry_date');
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('user');

        $table->addColumn('api_key', 'string')
            ->setNotnull(false);

        $table->addColumn('api_key_expiry_date', 'datetime')
            ->setNotnull(false);
    }
}
