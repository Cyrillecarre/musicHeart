<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241023090934 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CDF6E65AD');
        $this->addSql('DROP INDEX IDX_232B318CDF6E65AD ON game');
        $this->addSql('ALTER TABLE game CHANGE admin_id_id admin_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318C642B8210 FOREIGN KEY (admin_id) REFERENCES `admin` (id)');
        $this->addSql('CREATE INDEX IDX_232B318C642B8210 ON game (admin_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318C642B8210');
        $this->addSql('DROP INDEX IDX_232B318C642B8210 ON game');
        $this->addSql('ALTER TABLE game CHANGE admin_id admin_id_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CDF6E65AD FOREIGN KEY (admin_id_id) REFERENCES `admin` (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_232B318CDF6E65AD ON game (admin_id_id)');
    }
}
