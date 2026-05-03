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
            brandText: (string) $settings->getBrandText(),
            navItems: [
                ['label' => (string) $settings->getNavFeaturesLabel(), 'url' => (string) $settings->getNavFeaturesUrl()],
                ['label' => (string) $settings->getNavTemplatesLabel(), 'url' => (string) $settings->getNavTemplatesUrl()],
                ['label' => (string) $settings->getNavInvestorLabel(), 'url' => (string) $settings->getNavInvestorUrl()],
            ],
            eyebrowText: (string) $settings->getEyebrowText(),
            heroTitle: (string) $settings->getHeroTitle(),
            heroSubtitle: (string) $settings->getHeroSubtitle(),
            primaryCtaLabel: (string) $settings->getCtaLabel(),
            primaryCtaUrl: (string) $settings->getCtaUrl(),
            secondaryCtaLabel: (string) $settings->getSecondaryCtaLabel(),
            secondaryCtaUrl: (string) $settings->getSecondaryCtaUrl(),
            trustTags: $this->splitLines((string) $settings->getTrustTagsText()),
            previewKicker: (string) $settings->getPreviewKicker(),
            previewTitle: (string) $settings->getPreviewTitle(),
            previewStatus: (string) $settings->getPreviewStatus(),
            previewMetrics: [
                [
                    'label' => (string) $settings->getPreviewMetric1Label(),
                    'value' => (string) $settings->getPreviewMetric1Value(),
                    'note' => (string) $settings->getPreviewMetric1Note(),
                    'isPrimary' => true,
                ],
                [
                    'label' => (string) $settings->getPreviewMetric2Label(),
                    'value' => (string) $settings->getPreviewMetric2Value(),
                    'note' => (string) $settings->getPreviewMetric2Note(),
                    'isPrimary' => false,
                ],
                [
                    'label' => (string) $settings->getPreviewMetric3Label(),
                    'value' => (string) $settings->getPreviewMetric3Value(),
                    'note' => (string) $settings->getPreviewMetric3Note(),
                    'isPrimary' => false,
                ],
                [
                    'label' => (string) $settings->getPreviewMetric4Label(),
                    'value' => (string) $settings->getPreviewMetric4Value(),
                    'note' => (string) $settings->getPreviewMetric4Note(),
                    'isPrimary' => false,
                ],
            ],
            chartTitle: (string) $settings->getChartTitle(),
            chartPeriod: (string) $settings->getChartPeriod(),
            assistantNote: (string) $settings->getAssistantNote(),
            popupKicker: (string) $settings->getPopupKicker(),
            popupTitle: (string) $settings->getPopupTitle(),
            popupDescription: (string) $settings->getPopupDescription(),
            popupSteps: [
                ['number' => '01', 'title' => (string) $settings->getPopupStep1Title(), 'description' => (string) $settings->getPopupStep1Description()],
                ['number' => '02', 'title' => (string) $settings->getPopupStep2Title(), 'description' => (string) $settings->getPopupStep2Description()],
                ['number' => '03', 'title' => (string) $settings->getPopupStep3Title(), 'description' => (string) $settings->getPopupStep3Description()],
            ],
            popupPrimaryCtaLabel: (string) $settings->getPopupPrimaryCtaLabel(),
            popupPrimaryCtaUrl: (string) $settings->getPopupPrimaryCtaUrl(),
            popupSecondaryCtaLabel: (string) $settings->getPopupSecondaryCtaLabel(),
            seoTitle: (string) $settings->getSeoTitle(),
            seoDescription: (string) $settings->getSeoDescription(),
            ogTitle: $settings->getOgTitle(),
            ogDescription: $settings->getOgDescription(),
        );
    }

    /**
     * @return list<string>
     */
    private function splitLines(string $value): array
    {
        $lines = preg_split('/\R/u', $value) ?: [];

        return array_values(array_filter(
            array_map(static fn (string $line): string => trim($line), $lines),
            static fn (string $line): bool => '' !== $line,
        ));
    }
}
