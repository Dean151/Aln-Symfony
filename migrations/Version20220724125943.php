<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220724125943 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aln_feeder ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE aln_feeder ADD CONSTRAINT FK_1DDCA11A7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_1DDCA11A7E3C61F9 ON aln_feeder (owner_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE aln_feeder DROP FOREIGN KEY FK_1DDCA11A7E3C61F9');
        $this->addSql('DROP INDEX IDX_1DDCA11A7E3C61F9 ON aln_feeder');
        $this->addSql('ALTER TABLE aln_feeder DROP owner_id');
    }
}
