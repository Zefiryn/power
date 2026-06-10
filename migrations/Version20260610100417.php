<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260610100417 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create devices table with sequence and link it to reading table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE SEQUENCE devices_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE devices (id INT NOT NULL, is_current BOOLEAN NOT NULL DEFAULT TRUE, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE reading ADD COLUMN device_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE reading ADD CONSTRAINT FK_READING_DEVICE FOREIGN KEY (device_id) REFERENCES devices (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE reading DROP CONSTRAINT FK_READING_DEVICE');
        $this->addSql('ALTER TABLE reading DROP COLUMN device_id');
        $this->addSql('DROP TABLE devices');
        $this->addSql('DROP SEQUENCE devices_id_seq CASCADE');
    }
}
