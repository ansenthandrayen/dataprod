<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261007181522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE systeme ADD integre_le DATETIME NOT NULL, ADD integre_par_id INT NOT NULL');
        $this->addSql('ALTER TABLE systeme ADD CONSTRAINT FK_95796DE35654BB63 FOREIGN KEY (integre_par_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_95796DE35654BB63 ON systeme (integre_par_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE systeme DROP FOREIGN KEY FK_95796DE35654BB63');
        $this->addSql('DROP INDEX IDX_95796DE35654BB63 ON systeme');
        $this->addSql('ALTER TABLE systeme DROP integre_le, DROP integre_par_id');
    }
}
