<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261005162732 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE lot (id INT AUTO_INCREMENT NOT NULL, numero VARCHAR(100) NOT NULL, quantite_prevue INT NOT NULL, version_produit_id INT NOT NULL, UNIQUE INDEX UNIQ_B81291BF55AE19E (numero), INDEX IDX_B81291BA53085D6 (version_produit_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE nomenclature (id INT AUTO_INCREMENT NOT NULL, quantite INT NOT NULL, version_produit_id INT NOT NULL, reference_sous_ensemble_id INT NOT NULL, UNIQUE INDEX UNIQ_NOMENCLATURE_VERSION_SOUS_ENSEMBLE (version_produit_id, reference_sous_ensemble_id), INDEX IDX_799A3652A53085D6 (version_produit_id), INDEX IDX_799A3652BEEDB60D (reference_sous_ensemble_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reference_produit (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(50) NOT NULL, description VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_2ED5498FAEA34913 (reference), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE reference_sous_ensemble (id INT AUTO_INCREMENT NOT NULL, reference VARCHAR(50) NOT NULL, description VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_A7FD4199AEA34913 (reference), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE sous_ensemble (id INT AUTO_INCREMENT NOT NULL, numero_serie VARCHAR(100) NOT NULL, systeme_id INT NOT NULL, reference_sous_ensemble_id INT NOT NULL, UNIQUE INDEX UNIQ_840574E5565B809 (numero_serie), INDEX IDX_840574E5346F772E (systeme_id), INDEX IDX_840574E5BEEDB60D (reference_sous_ensemble_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE systeme (id INT AUTO_INCREMENT NOT NULL, numero_serie VARCHAR(100) NOT NULL, lot_id INT NOT NULL, UNIQUE INDEX UNIQ_95796DE3565B809 (numero_serie), INDEX IDX_95796DE3A8CBA5F7 (lot_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE version_produit (id INT AUTO_INCREMENT NOT NULL, lettre VARCHAR(10) NOT NULL, reference_produit_id INT NOT NULL, UNIQUE INDEX UNIQ_VERSION_PAR_REFERENCE (reference_produit_id, lettre), INDEX IDX_D56C09735B2A4BB4 (reference_produit_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE lot ADD CONSTRAINT FK_B81291BA53085D6 FOREIGN KEY (version_produit_id) REFERENCES version_produit (id)');
        $this->addSql('ALTER TABLE nomenclature ADD CONSTRAINT FK_799A3652A53085D6 FOREIGN KEY (version_produit_id) REFERENCES version_produit (id)');
        $this->addSql('ALTER TABLE nomenclature ADD CONSTRAINT FK_799A3652BEEDB60D FOREIGN KEY (reference_sous_ensemble_id) REFERENCES reference_sous_ensemble (id)');
        $this->addSql('ALTER TABLE sous_ensemble ADD CONSTRAINT FK_840574E5346F772E FOREIGN KEY (systeme_id) REFERENCES systeme (id)');
        $this->addSql('ALTER TABLE sous_ensemble ADD CONSTRAINT FK_840574E5BEEDB60D FOREIGN KEY (reference_sous_ensemble_id) REFERENCES reference_sous_ensemble (id)');
        $this->addSql('ALTER TABLE systeme ADD CONSTRAINT FK_95796DE3A8CBA5F7 FOREIGN KEY (lot_id) REFERENCES lot (id)');
        $this->addSql('ALTER TABLE version_produit ADD CONSTRAINT FK_D56C09735B2A4BB4 FOREIGN KEY (reference_produit_id) REFERENCES reference_produit (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lot DROP FOREIGN KEY FK_B81291BA53085D6');
        $this->addSql('ALTER TABLE nomenclature DROP FOREIGN KEY FK_799A3652A53085D6');
        $this->addSql('ALTER TABLE nomenclature DROP FOREIGN KEY FK_799A3652BEEDB60D');
        $this->addSql('ALTER TABLE sous_ensemble DROP FOREIGN KEY FK_840574E5346F772E');
        $this->addSql('ALTER TABLE sous_ensemble DROP FOREIGN KEY FK_840574E5BEEDB60D');
        $this->addSql('ALTER TABLE systeme DROP FOREIGN KEY FK_95796DE3A8CBA5F7');
        $this->addSql('ALTER TABLE version_produit DROP FOREIGN KEY FK_D56C09735B2A4BB4');
        $this->addSql('DROP TABLE lot');
        $this->addSql('DROP TABLE nomenclature');
        $this->addSql('DROP TABLE reference_produit');
        $this->addSql('DROP TABLE reference_sous_ensemble');
        $this->addSql('DROP TABLE sous_ensemble');
        $this->addSql('DROP TABLE systeme');
        $this->addSql('DROP TABLE version_produit');
    }
}
