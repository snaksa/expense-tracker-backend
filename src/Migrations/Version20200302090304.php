<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200302090304 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $table = $schema->getTable('transaction_record');

        $table->addColumn('wallet_receiver_id', 'integer')
            ->setNotnull(false)
            ->setUnsigned(true);

        $table->addForeignKeyConstraint(
            'wallet',
            ['wallet_receiver_id'],
            ['id'],
            ['onUpdate' => 'CASCADE', 'onDelete' => 'CASCADE'],
            'fk_transaction_record_wallet_receiver'
        );
    }

    public function down(Schema $schema) : void
    {
        $table = $schema->getTable('transaction_record');

        $table->dropColumn('wallet_receiver_id');
        $table->removeForeignKey('fk_transaction_record_wallet_receiver');
    }
}
