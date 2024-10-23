<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241022133758 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE `admin` (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE game (id INT AUTO_INCREMENT NOT NULL, admin_id_id INT DEFAULT NULL, end_date DATE NOT NULL, result_date DATE NOT NULL, INDEX IDX_232B318CDF6E65AD (admin_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE guess (id INT AUTO_INCREMENT NOT NULL, game_id_id INT DEFAULT NULL, music_url_id_id INT DEFAULT NULL, guessed_participant_id_id INT DEFAULT NULL, INDEX IDX_32D30F964D77E7D8 (game_id_id), INDEX IDX_32D30F96682A6781 (music_url_id_id), INDEX IDX_32D30F96ED76AEDB (guessed_participant_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participant (id INT AUTO_INCREMENT NOT NULL, admin_id_id INT DEFAULT NULL, patient_id_id INT DEFAULT NULL, participation_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, email VARCHAR(255) DEFAULT NULL, INDEX IDX_D79F6B11DF6E65AD (admin_id_id), INDEX IDX_D79F6B11EA724598 (patient_id_id), INDEX IDX_D79F6B116ACE3B73 (participation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE participation (id INT AUTO_INCREMENT NOT NULL, game_id_id INT DEFAULT NULL, music_url VARCHAR(255) NOT NULL, support_text VARCHAR(255) DEFAULT NULL, is_correct TINYINT(1) DEFAULT NULL, INDEX IDX_AB55E24F4D77E7D8 (game_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE patient (id INT AUTO_INCREMENT NOT NULL, admin_id_id INT NOT NULL, name VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, INDEX IDX_1ADAD7EBDF6E65AD (admin_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE game ADD CONSTRAINT FK_232B318CDF6E65AD FOREIGN KEY (admin_id_id) REFERENCES `admin` (id)');
        $this->addSql('ALTER TABLE guess ADD CONSTRAINT FK_32D30F964D77E7D8 FOREIGN KEY (game_id_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE guess ADD CONSTRAINT FK_32D30F96682A6781 FOREIGN KEY (music_url_id_id) REFERENCES participation (id)');
        $this->addSql('ALTER TABLE guess ADD CONSTRAINT FK_32D30F96ED76AEDB FOREIGN KEY (guessed_participant_id_id) REFERENCES participation (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11DF6E65AD FOREIGN KEY (admin_id_id) REFERENCES `admin` (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B11EA724598 FOREIGN KEY (patient_id_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE participant ADD CONSTRAINT FK_D79F6B116ACE3B73 FOREIGN KEY (participation_id) REFERENCES participation (id)');
        $this->addSql('ALTER TABLE participation ADD CONSTRAINT FK_AB55E24F4D77E7D8 FOREIGN KEY (game_id_id) REFERENCES game (id)');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EBDF6E65AD FOREIGN KEY (admin_id_id) REFERENCES `admin` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE game DROP FOREIGN KEY FK_232B318CDF6E65AD');
        $this->addSql('ALTER TABLE guess DROP FOREIGN KEY FK_32D30F964D77E7D8');
        $this->addSql('ALTER TABLE guess DROP FOREIGN KEY FK_32D30F96682A6781');
        $this->addSql('ALTER TABLE guess DROP FOREIGN KEY FK_32D30F96ED76AEDB');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11DF6E65AD');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B11EA724598');
        $this->addSql('ALTER TABLE participant DROP FOREIGN KEY FK_D79F6B116ACE3B73');
        $this->addSql('ALTER TABLE participation DROP FOREIGN KEY FK_AB55E24F4D77E7D8');
        $this->addSql('ALTER TABLE patient DROP FOREIGN KEY FK_1ADAD7EBDF6E65AD');
        $this->addSql('DROP TABLE `admin`');
        $this->addSql('DROP TABLE game');
        $this->addSql('DROP TABLE guess');
        $this->addSql('DROP TABLE participant');
        $this->addSql('DROP TABLE participation');
        $this->addSql('DROP TABLE patient');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
