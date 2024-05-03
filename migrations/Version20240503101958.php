<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240503101958 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE tag_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE reading_tag (reading_id INT NOT NULL, tag_id INT NOT NULL, PRIMARY KEY(reading_id, tag_id))');
        $this->addSql('CREATE INDEX IDX_8DDA1368527275CD ON reading_tag (reading_id)');
        $this->addSql('CREATE INDEX IDX_8DDA1368BAD26311 ON reading_tag (tag_id)');
        $this->addSql('CREATE TABLE tag (id INT NOT NULL, name VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->addSql('ALTER TABLE reading_tag ADD CONSTRAINT FK_8DDA1368527275CD FOREIGN KEY (reading_id) REFERENCES reading (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE reading_tag ADD CONSTRAINT FK_8DDA1368BAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE tag_id_seq CASCADE');
        $this->addSql('ALTER TABLE reading_tag DROP CONSTRAINT FK_8DDA1368527275CD');
        $this->addSql('ALTER TABLE reading_tag DROP CONSTRAINT FK_8DDA1368BAD26311');
        $this->addSql('DROP TABLE reading_tag');
        $this->addSql('DROP TABLE tag');
    }
}
