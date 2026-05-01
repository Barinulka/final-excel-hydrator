<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260501155500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align home page settings table with Doctrine mapping.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE home_page_settings ALTER seo_title TYPE VARCHAR(255)');
        $this->addSql('ALTER TABLE home_page_settings ALTER cta_url DROP DEFAULT');
        $this->addSql('ALTER TABLE home_page_settings ALTER seo_description DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE home_page_settings ALTER seo_title TYPE TEXT');
        $this->addSql("ALTER TABLE home_page_settings ALTER cta_url SET DEFAULT '/models'");
        $this->addSql("ALTER TABLE home_page_settings ALTER seo_description SET DEFAULT 'Умная система оценки инвестиционных проектов с персональным ИИ-помощником.'");
    }
}
