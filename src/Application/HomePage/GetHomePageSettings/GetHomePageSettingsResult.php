<?php

declare(strict_types=1);

namespace App\Application\HomePage\GetHomePageSettings;

final readonly class GetHomePageSettingsResult
{
    public function __construct(
        public string $heroTitle,
        public string $heroSubtitle,
        public string $ctaLabel,
        public string $ctaUrl,
        public string $seoTitle,
        public string $seoDescription,
        public ?string $ogTitle,
        public ?string $ogDescription,
    ) {
    }
}
