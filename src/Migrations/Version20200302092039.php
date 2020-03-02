<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200302092039 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->getTable('transaction_record');

        $table->getColumn('category_id')
            ->setNotnull(false);

        $table->getColumn('wallet_id')
            ->setNotnull(false);
    }

    public function down(Schema $schema) : void
    {
        $table = $schema->getTable('transaction_record');

        $table->getColumn('category_id')
            ->setNotnull(true);

        $table->getColumn('wallet_id')
            ->setNotnull(true);
    }
}
