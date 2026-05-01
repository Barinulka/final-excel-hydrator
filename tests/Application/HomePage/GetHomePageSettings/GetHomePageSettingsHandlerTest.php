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

        self::assertSame('Добро пожаловать', $result->heroTitle);
        self::assertSame("Умная система оценки инвестиционных проектов\nс персональным ИИ-помощником", $result->heroSubtitle);
        self::assertSame('Начать работу', $result->ctaLabel);
        self::assertSame('/models', $result->ctaUrl);
        self::assertSame('Добро пожаловать - ИнвестОценка', $result->seoTitle);
    }

    public function testReturnsPersistedSettings(): void
    {
        $settings = HomePageSettings::createDefault()
            ->setHeroTitle('Новый заголовок')
            ->setHeroSubtitle('Новый подзаголовок')
            ->setCtaLabel('Открыть модели')
            ->setCtaUrl('/models')
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

        self::assertSame('Новый заголовок', $result->heroTitle);
        self::assertSame('Новый подзаголовок', $result->heroSubtitle);
        self::assertSame('Открыть модели', $result->ctaLabel);
        self::assertSame('/models', $result->ctaUrl);
        self::assertSame('SEO title', $result->seoTitle);
        self::assertSame('SEO description', $result->seoDescription);
        self::assertNull($result->ogTitle);
        self::assertNull($result->ogDescription);
    }
}
