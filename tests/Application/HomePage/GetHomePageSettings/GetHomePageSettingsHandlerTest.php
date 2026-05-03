<?php

declare(strict_types=1);

namespace App\Tests\Application\HomePage\GetHomePageSettings;

use App\Application\HomePage\GetHomePageSettings\GetHomePageSettingsHandler;
use App\Application\HomePage\GetHomePageSettings\GetHomePageSettingsQuery;
use App\Application\HomePage\HomePageSettingsProvider;
use App\Entity\HomePageSettings;
use PHPUnit\Framework\TestCase;

final class GetHomePageSettingsHandlerTest extends TestCase
{
    public function testReturnsDefaultSettingsWhenEntityDoesNotExist(): void
    {
        $handler = new GetHomePageSettingsHandler(new class implements HomePageSettingsProvider {
            public function getSettings(): ?HomePageSettings
            {
                return null;
            }
        });

        $result = $handler->handle(new GetHomePageSettingsQuery());

        self::assertSame('ИнвестОценка', $result->brandText);
        self::assertSame('Создайте финансовую модель за несколько минут', $result->heroTitle);
        self::assertSame('Начать работу', $result->primaryCtaLabel);
        self::assertSame('/models', $result->primaryCtaUrl);
        self::assertSame(['NPV', 'IRR', 'Unit-экономика', 'Сценарии'], $result->trustTags);
        self::assertSame('Кофейня у бизнес-центра', $result->previewTitle);
        self::assertSame('NPV', $result->previewMetrics[0]['label']);
        self::assertTrue($result->previewMetrics[0]['isPrimary']);
        self::assertCount(3, $result->popupSteps);
        self::assertSame('Начнем с первой финансовой модели', $result->popupTitle);
        self::assertSame('ИнвестОценка - финансовые модели для бизнеса', $result->seoTitle);
    }

    public function testReturnsPersistedSettings(): void
    {
        $settings = HomePageSettings::createDefault()
            ->setBrandText('Мой сервис')
            ->setHeroTitle('Новый заголовок')
            ->setHeroSubtitle('Новый подзаголовок')
            ->setCtaLabel('Открыть модели')
            ->setCtaUrl('/models')
            ->setSecondaryCtaLabel('Демо')
            ->setSecondaryCtaUrl('#demo')
            ->setTrustTagsText("A\nB\n\nC")
            ->setPreviewTitle('Тестовая модель')
            ->setPopupTitle('Тестовый попап')
            ->setSeoTitle('SEO title')
            ->setSeoDescription('SEO description')
            ->setOgTitle(null)
            ->setOgDescription(null);

        $handler = new GetHomePageSettingsHandler(new class($settings) implements HomePageSettingsProvider {
            public function __construct(
                private readonly HomePageSettings $settings,
            ) {
            }

            public function getSettings(): ?HomePageSettings
            {
                return $this->settings;
            }
        });

        $result = $handler->handle(new GetHomePageSettingsQuery());

        self::assertSame('Мой сервис', $result->brandText);
        self::assertSame('Новый заголовок', $result->heroTitle);
        self::assertSame('Новый подзаголовок', $result->heroSubtitle);
        self::assertSame('Открыть модели', $result->primaryCtaLabel);
        self::assertSame('/models', $result->primaryCtaUrl);
        self::assertSame('Демо', $result->secondaryCtaLabel);
        self::assertSame('#demo', $result->secondaryCtaUrl);
        self::assertSame(['A', 'B', 'C'], $result->trustTags);
        self::assertSame('Тестовая модель', $result->previewTitle);
        self::assertSame('Тестовый попап', $result->popupTitle);
        self::assertSame('SEO title', $result->seoTitle);
        self::assertSame('SEO description', $result->seoDescription);
        self::assertNull($result->ogTitle);
        self::assertNull($result->ogDescription);
    }
}
