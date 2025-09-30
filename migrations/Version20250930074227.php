<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250930074227 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE lieu ALTER rue TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE site ALTER nom TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE sortie ALTER nom TYPE VARCHAR(50)');
        $this->addSql('ALTER TABLE sortie ALTER infos_sortie TYPE VARCHAR(250)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_VILLE ON ville (nom)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE sortie ALTER nom TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE sortie ALTER infos_sortie TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE site ALTER nom TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE lieu ALTER rue TYPE VARCHAR(255)');
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_VILLE');
    }
}
