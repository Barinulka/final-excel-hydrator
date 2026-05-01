<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260501073427 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add CalculationResult snapshot payload to Excel export jobs.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE excel_exports ADD calculation_result_payload JSON DEFAULT \'{"tables":[],"metrics":{},"warnings":[]}\'::json NOT NULL');
        $this->addSql('ALTER TABLE excel_exports ALTER calculation_result_payload DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE excel_exports DROP calculation_result_payload');
    }
}
