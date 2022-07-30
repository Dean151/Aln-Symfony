<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220730172558 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE aln_manual_meal (id INT AUTO_INCREMENT NOT NULL, feeder_id INT NOT NULL, distributed_on DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', amount SMALLINT NOT NULL, previous_meal VARCHAR(5) DEFAULT NULL, INDEX IDX_B2B660221274E059 (feeder_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE aln_planned_meal (id INT AUTO_INCREMENT NOT NULL, feeder_id INT NOT NULL, planning_id INT NOT NULL, time VARCHAR(5) DEFAULT NULL, amount SMALLINT NOT NULL, is_enabled TINYINT(1) NOT NULL, INDEX IDX_4B9EA6C71274E059 (feeder_id), INDEX IDX_4B9EA6C73D865311 (planning_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE aln_manual_meal ADD CONSTRAINT FK_B2B660221274E059 FOREIGN KEY (feeder_id) REFERENCES aln_feeder (id)');
        $this->addSql('ALTER TABLE aln_planned_meal ADD CONSTRAINT FK_4B9EA6C71274E059 FOREIGN KEY (feeder_id) REFERENCES aln_feeder (id)');
        $this->addSql('ALTER TABLE aln_planned_meal ADD CONSTRAINT FK_4B9EA6C73D865311 FOREIGN KEY (planning_id) REFERENCES aln_planning (id)');
        $this->addSql('DROP TABLE aln_meal');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE aln_meal (id INT AUTO_INCREMENT NOT NULL, feeder_id INT NOT NULL, planning_id INT DEFAULT NULL, distributed_on DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', time VARCHAR(5) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, amount SMALLINT NOT NULL, is_enabled TINYINT(1) NOT NULL, INDEX IDX_27A3E6D1274E059 (feeder_id), INDEX IDX_27A3E6D3D865311 (planning_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE aln_meal ADD CONSTRAINT FK_27A3E6D1274E059 FOREIGN KEY (feeder_id) REFERENCES aln_feeder (id)');
        $this->addSql('ALTER TABLE aln_meal ADD CONSTRAINT FK_27A3E6D3D865311 FOREIGN KEY (planning_id) REFERENCES aln_planning (id)');
        $this->addSql('DROP TABLE aln_manual_meal');
        $this->addSql('DROP TABLE aln_planned_meal');
    }
}
