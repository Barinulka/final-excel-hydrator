<?php

declare(strict_types=1);

namespace App\Application\HomePage\GetHomePageSettings;

use App\Application\HomePage\HomePageSettingsProvider;
use App\Entity\HomePageSettings;

final readonly class GetHomePageSettingsHandler
{
    public function __construct(
        private HomePageSettingsProvider $homePageSettingsProvider,
    ) {
    }

    public function handle(GetHomePageSettingsQuery $query): GetHomePageSettingsResult
    {
        $settings = $this->homePageSettingsProvider->getSettings() ?? HomePageSettings::createDefault();

        return new GetHomePageSettingsResult(
            heroTitle: (string) $settings->getHeroTitle(),
            heroSubtitle: (string) $settings->getHeroSubtitle(),
            ctaLabel: (string) $settings->getCtaLabel(),
            ctaUrl: (string) $settings->getCtaUrl(),
            seoTitle: (string) $settings->getSeoTitle(),
            seoDescription: (string) $settings->getSeoDescription(),
            ogTitle: $settings->getOgTitle(),
            ogDescription: $settings->getOgDescription(),
        );
    }
}
