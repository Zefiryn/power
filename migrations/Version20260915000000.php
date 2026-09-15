<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260915000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add use_decimal flag to devices table.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE devices ADD COLUMN use_decimal BOOLEAN NOT NULL DEFAULT TRUE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE devices DROP COLUMN use_decimal');
    }
}
