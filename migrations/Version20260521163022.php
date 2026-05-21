<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260521163022 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE excel_exports DROP CONSTRAINT excel_exports__project_id__fk');
        $this->addSql('ALTER TABLE excel_exports ALTER project_id DROP NOT NULL');
        $this->addSql('ALTER TABLE excel_exports ADD CONSTRAINT FK_8705BF2C166D1F9C FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE SET NULL NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE excel_exports DROP CONSTRAINT FK_8705BF2C166D1F9C');
        $this->addSql('ALTER TABLE excel_exports ALTER project_id SET NOT NULL');
        $this->addSql('ALTER TABLE excel_exports ADD CONSTRAINT excel_exports__project_id__fk FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }
}
