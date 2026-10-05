<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261005220546 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE nomenclature_version_sous_ensemble (nomenclature_id INT NOT NULL, version_sous_ensemble_id INT NOT NULL, INDEX IDX_FECBD1690BFD4B8 (nomenclature_id), INDEX IDX_FECBD162D0AA7C7 (version_sous_ensemble_id), PRIMARY KEY (nomenclature_id, version_sous_ensemble_id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE version_sous_ensemble (id INT AUTO_INCREMENT NOT NULL, lettre VARCHAR(10) NOT NULL, reference_sous_ensemble_id INT NOT NULL, UNIQUE INDEX UNIQ_VERSION_SOUS_ENSEMBLE_PAR_REFERENCE (reference_sous_ensemble_id, lettre), INDEX IDX_44B55D93BEEDB60D (reference_sous_ensemble_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE nomenclature_version_sous_ensemble ADD CONSTRAINT FK_FECBD1690BFD4B8 FOREIGN KEY (nomenclature_id) REFERENCES nomenclature (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE nomenclature_version_sous_ensemble ADD CONSTRAINT FK_FECBD162D0AA7C7 FOREIGN KEY (version_sous_ensemble_id) REFERENCES version_sous_ensemble (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE version_sous_ensemble ADD CONSTRAINT FK_44B55D93BEEDB60D FOREIGN KEY (reference_sous_ensemble_id) REFERENCES reference_sous_ensemble (id)');
        $this->addSql('ALTER TABLE sous_ensemble DROP FOREIGN KEY `FK_840574E5BEEDB60D`');
        $this->addSql('DROP INDEX IDX_840574E5BEEDB60D ON sous_ensemble');
        $this->addSql('ALTER TABLE sous_ensemble CHANGE reference_sous_ensemble_id version_sous_ensemble_id INT NOT NULL');
        $this->addSql('ALTER TABLE sous_ensemble ADD CONSTRAINT FK_840574E52D0AA7C7 FOREIGN KEY (version_sous_ensemble_id) REFERENCES version_sous_ensemble (id)');
        $this->addSql('CREATE INDEX IDX_840574E52D0AA7C7 ON sous_ensemble (version_sous_ensemble_id)');
    }

    public function down(Schema $schema): void
    {
        // 1) D'abord détacher sous_ensemble de version_sous_ensemble
        $this->addSql('ALTER TABLE sous_ensemble DROP FOREIGN KEY FK_840574E52D0AA7C7');
        $this->addSql('DROP INDEX IDX_840574E52D0AA7C7 ON sous_ensemble');

        // 2) Ensuite supprimer les nouvelles tables
        $this->addSql('ALTER TABLE nomenclature_version_sous_ensemble DROP FOREIGN KEY FK_FECBD1690BFD4B8');
        $this->addSql('ALTER TABLE nomenclature_version_sous_ensemble DROP FOREIGN KEY FK_FECBD162D0AA7C7');
        $this->addSql('ALTER TABLE version_sous_ensemble DROP FOREIGN KEY FK_44B55D93BEEDB60D');
        $this->addSql('DROP TABLE nomenclature_version_sous_ensemble');
        $this->addSql('DROP TABLE version_sous_ensemble');

        // 3) Enfin, remettre l'ancienne colonne et son lien
        $this->addSql('ALTER TABLE sous_ensemble CHANGE version_sous_ensemble_id reference_sous_ensemble_id INT NOT NULL');
        $this->addSql('ALTER TABLE sous_ensemble ADD CONSTRAINT `FK_840574E5BEEDB60D` FOREIGN KEY (reference_sous_ensemble_id) REFERENCES reference_sous_ensemble (id)');
        $this->addSql('CREATE INDEX IDX_840574E5BEEDB60D ON sous_ensemble (reference_sous_ensemble_id)');
    }
}
