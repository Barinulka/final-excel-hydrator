<?php

declare(strict_types=1);

namespace App\Application\HomePage\GetHomePageSettings;

final readonly class GetHomePageSettingsResult
{
    /**
     * @param list<array{label: string, url: string}> $navItems
     * @param list<string> $trustTags
     * @param list<array{label: string, value: string, note: string, isPrimary: bool}> $previewMetrics
     * @param list<array{number: string, title: string, description: string}> $popupSteps
     */
    public function __construct(
        public string $brandText,
        public array $navItems,
        public string $eyebrowText,
        public string $heroTitle,
        public string $heroSubtitle,
        public string $primaryCtaLabel,
        public string $primaryCtaUrl,
        public string $secondaryCtaLabel,
        public string $secondaryCtaUrl,
        public array $trustTags,
        public string $previewKicker,
        public string $previewTitle,
        public string $previewStatus,
        public array $previewMetrics,
        public string $chartTitle,
        public string $chartPeriod,
        public string $assistantNote,
        public string $popupKicker,
        public string $popupTitle,
        public string $popupDescription,
        public array $popupSteps,
        public string $popupPrimaryCtaLabel,
        public string $popupPrimaryCtaUrl,
        public string $popupSecondaryCtaLabel,
        public string $seoTitle,
        public string $seoDescription,
        public ?string $ogTitle,
        public ?string $ogDescription,
    ) {
    }
}
