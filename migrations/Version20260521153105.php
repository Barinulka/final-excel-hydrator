<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260521153105 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE financial_models DROP CONSTRAINT fk_4f766cba166d1f9c');
        $this->addSql('ALTER TABLE financial_models ALTER project_id DROP NOT NULL');
        $this->addSql('ALTER TABLE financial_models ADD CONSTRAINT FK_4F766CBA166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE SET NULL NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE financial_models DROP CONSTRAINT FK_4F766CBA166D1F9C');
        $this->addSql('ALTER TABLE financial_models ALTER project_id SET NOT NULL');
        $this->addSql('ALTER TABLE financial_models ADD CONSTRAINT fk_4f766cba166d1f9c FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
