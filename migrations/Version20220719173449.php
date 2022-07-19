<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220719173449 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create tables for meals and plannings';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE aln_feeder (id INT AUTO_INCREMENT NOT NULL, identifier VARCHAR(16) NOT NULL, name VARCHAR(255) NOT NULL, last_seen DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', default_meal_amount SMALLINT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE aln_meal (id INT AUTO_INCREMENT NOT NULL, feeder_id INT NOT NULL, planning_id INT DEFAULT NULL, distributed_on DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', amount SMALLINT NOT NULL, is_enabled TINYINT(1) NOT NULL, time LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', INDEX IDX_27A3E6D1274E059 (feeder_id), INDEX IDX_27A3E6D3D865311 (planning_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE aln_planning (id INT AUTO_INCREMENT NOT NULL, feeder_id INT NOT NULL, created_on DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_4192380C1274E059 (feeder_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE aln_meal ADD CONSTRAINT FK_27A3E6D1274E059 FOREIGN KEY (feeder_id) REFERENCES aln_feeder (id)');
        $this->addSql('ALTER TABLE aln_meal ADD CONSTRAINT FK_27A3E6D3D865311 FOREIGN KEY (planning_id) REFERENCES aln_planning (id)');
        $this->addSql('ALTER TABLE aln_planning ADD CONSTRAINT FK_4192380C1274E059 FOREIGN KEY (feeder_id) REFERENCES aln_feeder (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aln_meal DROP FOREIGN KEY FK_27A3E6D1274E059');
        $this->addSql('ALTER TABLE aln_planning DROP FOREIGN KEY FK_4192380C1274E059');
        $this->addSql('ALTER TABLE aln_meal DROP FOREIGN KEY FK_27A3E6D3D865311');
        $this->addSql('DROP TABLE aln_feeder');
        $this->addSql('DROP TABLE aln_meal');
        $this->addSql('DROP TABLE aln_planning');
    }
}
