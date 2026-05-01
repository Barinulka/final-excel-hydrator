<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260501154500 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Rename home page admin settings table and split content fields from SEO fields.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE home RENAME TO home_page_settings');
        $this->addSql('ALTER TABLE home_page_settings RENAME COLUMN page_name TO hero_title');
        $this->addSql('ALTER TABLE home_page_settings RENAME COLUMN description TO hero_subtitle');
        $this->addSql('ALTER TABLE home_page_settings RENAME COLUMN btn_text TO cta_label');
        $this->addSql('ALTER TABLE home_page_settings RENAME COLUMN title TO seo_title');
        $this->addSql("UPDATE home_page_settings SET hero_subtitle = COALESCE(hero_subtitle, 'Умная система оценки инвестиционных проектов' || chr(10) || 'с персональным ИИ-помощником')");
        $this->addSql("UPDATE home_page_settings SET cta_label = COALESCE(cta_label, 'Начать работу')");
        $this->addSql("UPDATE home_page_settings SET seo_title = COALESCE(seo_title, 'Добро пожаловать - ИнвестОценка')");
        $this->addSql("ALTER TABLE home_page_settings ADD cta_url VARCHAR(255) DEFAULT '/models' NOT NULL");
        $this->addSql("ALTER TABLE home_page_settings ADD seo_description TEXT DEFAULT 'Умная система оценки инвестиционных проектов с персональным ИИ-помощником.' NOT NULL");
        $this->addSql('ALTER TABLE home_page_settings ADD og_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD og_description TEXT DEFAULT NULL');
        $this->addSql("UPDATE home_page_settings SET og_title = COALESCE(og_title, 'ИнвестОценка')");
        $this->addSql("UPDATE home_page_settings SET og_description = COALESCE(og_description, 'Умная система оценки инвестиционных проектов с персональным ИИ-помощником.')");
        $this->addSql('ALTER TABLE home_page_settings ALTER hero_subtitle SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER cta_label SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER seo_title SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings DROP keywords');
        $this->addSql('ALTER TABLE home_page_settings DROP robots');
        $this->addSql('ALTER TABLE home_page_settings DROP canonical');
        $this->addSql('ALTER TABLE home_page_settings DROP h1');
        $this->addSql("INSERT INTO home_page_settings (id, hero_title, hero_subtitle, cta_label, cta_url, seo_title, seo_description, og_title, og_description, created_at, updated_at) SELECT 1, 'Добро пожаловать', 'Умная система оценки инвестиционных проектов' || chr(10) || 'с персональным ИИ-помощником', 'Начать работу', '/models', 'Добро пожаловать - ИнвестОценка', 'Умная система оценки инвестиционных проектов с персональным ИИ-помощником.', 'ИнвестОценка', 'Умная система оценки инвестиционных проектов с персональным ИИ-помощником.', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP WHERE NOT EXISTS (SELECT 1 FROM home_page_settings WHERE id = 1)");
        $this->addSql("SELECT setval(pg_get_serial_sequence('home_page_settings', 'id'), (SELECT MAX(id) FROM home_page_settings))");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE home_page_settings ADD keywords TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD robots TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD canonical VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD h1 VARCHAR(600) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER hero_subtitle DROP NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER cta_label DROP NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER seo_title DROP NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings DROP cta_url');
        $this->addSql('ALTER TABLE home_page_settings DROP seo_description');
        $this->addSql('ALTER TABLE home_page_settings DROP og_title');
        $this->addSql('ALTER TABLE home_page_settings DROP og_description');
        $this->addSql('ALTER TABLE home_page_settings RENAME COLUMN hero_title TO page_name');
        $this->addSql('ALTER TABLE home_page_settings RENAME COLUMN hero_subtitle TO description');
        $this->addSql('ALTER TABLE home_page_settings RENAME COLUMN cta_label TO btn_text');
        $this->addSql('ALTER TABLE home_page_settings RENAME COLUMN seo_title TO title');
        $this->addSql('ALTER TABLE home_page_settings RENAME TO home');
    }
}
