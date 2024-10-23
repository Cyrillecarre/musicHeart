<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241023090044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11DF6E65AD');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11EA724598');
        $this->addSql('DROP INDEX IDX_D79F6B11DF6E65AD ON participant');
        $this->addSql('DROP INDEX IDX_D79F6B11EA724598 ON participant');
        $this->addSql('ALTER TABLE participant ADD admin_id INT DEFAULT NULL, ADD patient_id INT DEFAULT NULL, DROP admin_id_id, DROP patient_id_id');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11642B8210 FOREIGN KEY (admin_id) REFERENCES `admin` (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B116B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('CREATE INDEX IDX_D79F6B11642B8210 ON participant (admin_id)');
        $this->addSql('CREATE INDEX IDX_D79F6B116B899279 ON participant (patient_id)');
        $this->addSql('ALTER TABLE patient DROP FOREIGN KEY FK_1ADAD7EBDF6E65AD');
        $this->addSql('DROP INDEX IDX_1ADAD7EBDF6E65AD ON patient');
        $this->addSql('ALTER TABLE patient CHANGE admin_id_id admin_id INT NOT NULL');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EB642B8210 FOREIGN KEY (admin_id) REFERENCES `admin` (id)');
        $this->addSql('CREATE INDEX IDX_1ADAD7EB642B8210 ON patient (admin_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE patient DROP FOREIGN KEY FK_1ADAD7EB642B8210');
        $this->addSql('DROP INDEX IDX_1ADAD7EB642B8210 ON patient');
        $this->addSql('ALTER TABLE patient CHANGE admin_id admin_id_id INT NOT NULL');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EBDF6E65AD FOREIGN KEY (admin_id_id) REFERENCES `admin` (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_1ADAD7EBDF6E65AD ON patient (admin_id_id)');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11642B8210');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B116B899279');
        $this->addSql('DROP INDEX IDX_D79F6B11642B8210 ON participant');
        $this->addSql('DROP INDEX IDX_D79F6B116B899279 ON participant');
        $this->addSql('ALTER TABLE participant ADD admin_id_id INT DEFAULT NULL, ADD patient_id_id INT DEFAULT NULL, DROP admin_id, DROP patient_id');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11DF6E65AD FOREIGN KEY (admin_id_id) REFERENCES `admin` (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11EA724598 FOREIGN KEY (patient_id_id) REFERENCES patient (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_D79F6B11DF6E65AD ON participant (admin_id_id)');
        $this->addSql('CREATE INDEX IDX_D79F6B11EA724598 ON participant (patient_id_id)');
    }
}
