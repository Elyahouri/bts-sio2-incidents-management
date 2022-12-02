<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221202083305 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE incident ADD followed_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE incident ADD CONSTRAINT FK_3D03A11A3970CDB6 FOREIGN KEY (followed_by_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_3D03A11A3970CDB6 ON incident (followed_by_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE incident DROP FOREIGN KEY FK_3D03A11A3970CDB6');
        $this->addSql('DROP INDEX IDX_3D03A11A3970CDB6 ON incident');
        $this->addSql('ALTER TABLE incident DROP followed_by_id');
    }
}
