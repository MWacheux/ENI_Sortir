<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250923082127 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE participant ADD email VARCHAR(180) NOT NULL, ADD roles JSON NOT NULL, CHANGE site_id site_id INT DEFAULT NULL, CHANGE telephone telephone VARCHAR(10) DEFAULT NULL, CHANGE administrateur administrateur TINYINT(1) DEFAULT NULL, CHANGE actif actif TINYINT(1) DEFAULT NULL, CHANGE mail password VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL ON participant (email)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_EMAIL ON participant');
        $this->addSql('ALTER TABLE participant DROP email, DROP roles, CHANGE site_id site_id INT NOT NULL, CHANGE telephone telephone VARCHAR(10) NOT NULL, CHANGE administrateur administrateur TINYINT(1) NOT NULL, CHANGE actif actif TINYINT(1) NOT NULL, CHANGE password mail VARCHAR(255) NOT NULL');
    }
}
