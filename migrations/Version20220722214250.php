<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220722214250 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Created table for storing alerts';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE aln_alert (id INT AUTO_INCREMENT NOT NULL, feeder_id INT DEFAULT NULL, type VARCHAR(255) NOT NULL, triggered_on DATETIME NOT NULL, message LONGTEXT DEFAULT NULL, time VARCHAR(5) DEFAULT NULL, amount SMALLINT DEFAULT NULL, INDEX IDX_DDDB08FB1274E059 (feeder_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE aln_alert ADD CONSTRAINT FK_DDDB08FB1274E059 FOREIGN KEY (feeder_id) REFERENCES aln_feeder (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE aln_alert');
    }
}
