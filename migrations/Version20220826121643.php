<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220826121643 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aln_planned_meal DROP FOREIGN KEY FK_4B9EA6C71274E059');
        $this->addSql('DROP INDEX IDX_4B9EA6C71274E059 ON aln_planned_meal');
        $this->addSql('ALTER TABLE aln_planned_meal DROP feeder_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aln_planned_meal ADD feeder_id INT NOT NULL');
        $this->addSql('ALTER TABLE aln_planned_meal ADD CONSTRAINT FK_4B9EA6C71274E059 FOREIGN KEY (feeder_id) REFERENCES aln_feeder (id)');
        $this->addSql('CREATE INDEX IDX_4B9EA6C71274E059 ON aln_planned_meal (feeder_id)');
    }
}
