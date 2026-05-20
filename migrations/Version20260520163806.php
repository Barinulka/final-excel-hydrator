<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260520163806 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE financial_models ADD owner_id BIGINT DEFAULT NULL');

        $this->addSql('
            UPDATE financial_models fm
            SET owner_id = p.owner_id
            FROM projects p
            WHERE fm.project_id = p.id
        ');

        $this->addSql('ALTER TABLE financial_models ALTER owner_id SET NOT NULL');
        $this->addSql('CREATE INDEX financial_models__owner_id__idx ON financial_models (owner_id)');
        $this->addSql('ALTER TABLE financial_models ADD CONSTRAINT financial_models__owner_id__fk FOREIGN KEY (owner_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE financial_models DROP CONSTRAINT financial_models__owner_id__fk');
        $this->addSql('DROP INDEX financial_models__owner_id__idx');
        $this->addSql('ALTER TABLE financial_models DROP owner_id');
    }
}
