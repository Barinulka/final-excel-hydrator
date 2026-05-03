<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260502120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Extend home page settings for the new landing page with popup.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE home_page_settings ADD brand_text VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD nav_features_label VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD nav_features_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD nav_templates_label VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD nav_templates_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD nav_investor_label VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD nav_investor_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD eyebrow_text VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD secondary_cta_label VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD secondary_cta_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD trust_tags_text TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD preview_kicker VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD preview_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD preview_status VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD preview_metric1_label VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD preview_metric1_value VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD preview_metric1_note VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD preview_metric2_label VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD preview_metric2_value VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD preview_metric2_note VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD preview_metric3_label VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD preview_metric3_value VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD preview_metric3_note VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD preview_metric4_label VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD preview_metric4_value VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD preview_metric4_note VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD chart_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD chart_period VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD assistant_note TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD popup_kicker VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD popup_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD popup_description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD popup_step1_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD popup_step1_description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD popup_step2_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD popup_step2_description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD popup_step3_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD popup_step3_description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD popup_primary_cta_label VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD popup_primary_cta_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE home_page_settings ADD popup_secondary_cta_label VARCHAR(255) DEFAULT NULL');

        $this->addSql("UPDATE home_page_settings SET hero_title = 'Создайте финансовую модель за несколько минут', hero_subtitle = 'Соберите прогноз, проверьте окупаемость и подготовьте проект к разговору с партнерами, банком или инвестором.', cta_label = 'Начать работу', cta_url = '/models', seo_title = 'ИнвестОценка - финансовые модели для бизнеса', seo_description = 'Сервис для создания финансовых моделей, оценки окупаемости и подготовки инвестиционных решений.', og_title = 'ИнвестОценка', og_description = 'Создайте финансовую модель, проверьте окупаемость и подготовьте проект к разговору с инвестором.'");
        $this->addSql("UPDATE home_page_settings SET brand_text = 'ИнвестОценка', nav_features_label = 'Возможности', nav_features_url = '#features', nav_templates_label = 'Шаблоны', nav_templates_url = '#templates', nav_investor_label = 'Для инвестора', nav_investor_url = '#investor', eyebrow_text = 'Рабочее пространство для финансовых решений', secondary_cta_label = 'Посмотреть пример', secondary_cta_url = '#example', trust_tags_text = 'NPV' || chr(10) || 'IRR' || chr(10) || 'Unit-экономика' || chr(10) || 'Сценарии'");
        $this->addSql("UPDATE home_page_settings SET preview_kicker = 'Новая модель', preview_title = 'Кофейня у бизнес-центра', preview_status = 'Готово к оценке', preview_metric1_label = 'NPV', preview_metric1_value = '₽ 18,4 млн', preview_metric1_note = 'базовый сценарий', preview_metric2_label = 'IRR', preview_metric2_value = '27%', preview_metric2_note = 'выше ставки капитала', preview_metric3_label = 'Окупаемость', preview_metric3_value = '3,2 года', preview_metric3_note = 'с учетом CAPEX', preview_metric4_label = 'EBITDA', preview_metric4_value = '21%', preview_metric4_note = 'на 3-й год', chart_title = 'Прогноз денежного потока', chart_period = '2026-2030', assistant_note = 'Маржинальность чувствительна к аренде. Проверьте сценарий +12% к фиксированным расходам.'");
        $this->addSql("UPDATE home_page_settings SET popup_kicker = 'Добро пожаловать', popup_title = 'Начнем с первой финансовой модели', popup_description = 'У вас пока нет созданных моделей. Поможем собрать структуру проекта, рассчитать ключевые показатели и подготовить понятную оценку для принятия решения.', popup_step1_title = 'Опишите проект', popup_step1_description = 'сфера, формат, стартовые вложения', popup_step2_title = 'Проверьте сценарии', popup_step2_description = 'выручка, расходы, чувствительность', popup_step3_title = 'Получите вывод', popup_step3_description = 'NPV, IRR, окупаемость и риски', popup_primary_cta_label = 'Создать первую модель', popup_primary_cta_url = '/models', popup_secondary_cta_label = 'Остаться на главном экране'");

        $this->addSql('ALTER TABLE home_page_settings ALTER brand_text SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER nav_features_label SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER nav_features_url SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER nav_templates_label SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER nav_templates_url SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER nav_investor_label SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER nav_investor_url SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER eyebrow_text SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER secondary_cta_label SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER secondary_cta_url SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER trust_tags_text SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER preview_kicker SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER preview_title SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER preview_status SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER preview_metric1_label SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER preview_metric1_value SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER preview_metric1_note SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER preview_metric2_label SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER preview_metric2_value SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER preview_metric2_note SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER preview_metric3_label SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER preview_metric3_value SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER preview_metric3_note SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER preview_metric4_label SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER preview_metric4_value SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER preview_metric4_note SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER chart_title SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER chart_period SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER assistant_note SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER popup_kicker SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER popup_title SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER popup_description SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER popup_step1_title SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER popup_step1_description SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER popup_step2_title SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER popup_step2_description SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER popup_step3_title SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER popup_step3_description SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER popup_primary_cta_label SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER popup_primary_cta_url SET NOT NULL');
        $this->addSql('ALTER TABLE home_page_settings ALTER popup_secondary_cta_label SET NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE home_page_settings SET hero_title = 'Добро пожаловать', hero_subtitle = 'Умная система оценки инвестиционных проектов' || chr(10) || 'с персональным ИИ-помощником', cta_label = 'Начать работу', cta_url = '/models', seo_title = 'Добро пожаловать - ИнвестОценка', seo_description = 'Умная система оценки инвестиционных проектов с персональным ИИ-помощником.', og_title = 'ИнвестОценка', og_description = 'Умная система оценки инвестиционных проектов с персональным ИИ-помощником.'");

        $this->addSql('ALTER TABLE home_page_settings DROP brand_text');
        $this->addSql('ALTER TABLE home_page_settings DROP nav_features_label');
        $this->addSql('ALTER TABLE home_page_settings DROP nav_features_url');
        $this->addSql('ALTER TABLE home_page_settings DROP nav_templates_label');
        $this->addSql('ALTER TABLE home_page_settings DROP nav_templates_url');
        $this->addSql('ALTER TABLE home_page_settings DROP nav_investor_label');
        $this->addSql('ALTER TABLE home_page_settings DROP nav_investor_url');
        $this->addSql('ALTER TABLE home_page_settings DROP eyebrow_text');
        $this->addSql('ALTER TABLE home_page_settings DROP secondary_cta_label');
        $this->addSql('ALTER TABLE home_page_settings DROP secondary_cta_url');
        $this->addSql('ALTER TABLE home_page_settings DROP trust_tags_text');
        $this->addSql('ALTER TABLE home_page_settings DROP preview_kicker');
        $this->addSql('ALTER TABLE home_page_settings DROP preview_title');
        $this->addSql('ALTER TABLE home_page_settings DROP preview_status');
        $this->addSql('ALTER TABLE home_page_settings DROP preview_metric1_label');
        $this->addSql('ALTER TABLE home_page_settings DROP preview_metric1_value');
        $this->addSql('ALTER TABLE home_page_settings DROP preview_metric1_note');
        $this->addSql('ALTER TABLE home_page_settings DROP preview_metric2_label');
        $this->addSql('ALTER TABLE home_page_settings DROP preview_metric2_value');
        $this->addSql('ALTER TABLE home_page_settings DROP preview_metric2_note');
        $this->addSql('ALTER TABLE home_page_settings DROP preview_metric3_label');
        $this->addSql('ALTER TABLE home_page_settings DROP preview_metric3_value');
        $this->addSql('ALTER TABLE home_page_settings DROP preview_metric3_note');
        $this->addSql('ALTER TABLE home_page_settings DROP preview_metric4_label');
        $this->addSql('ALTER TABLE home_page_settings DROP preview_metric4_value');
        $this->addSql('ALTER TABLE home_page_settings DROP preview_metric4_note');
        $this->addSql('ALTER TABLE home_page_settings DROP chart_title');
        $this->addSql('ALTER TABLE home_page_settings DROP chart_period');
        $this->addSql('ALTER TABLE home_page_settings DROP assistant_note');
        $this->addSql('ALTER TABLE home_page_settings DROP popup_kicker');
        $this->addSql('ALTER TABLE home_page_settings DROP popup_title');
        $this->addSql('ALTER TABLE home_page_settings DROP popup_description');
        $this->addSql('ALTER TABLE home_page_settings DROP popup_step1_title');
        $this->addSql('ALTER TABLE home_page_settings DROP popup_step1_description');
        $this->addSql('ALTER TABLE home_page_settings DROP popup_step2_title');
        $this->addSql('ALTER TABLE home_page_settings DROP popup_step2_description');
        $this->addSql('ALTER TABLE home_page_settings DROP popup_step3_title');
        $this->addSql('ALTER TABLE home_page_settings DROP popup_step3_description');
        $this->addSql('ALTER TABLE home_page_settings DROP popup_primary_cta_label');
        $this->addSql('ALTER TABLE home_page_settings DROP popup_primary_cta_url');
        $this->addSql('ALTER TABLE home_page_settings DROP popup_secondary_cta_label');
    }
}
