<?php

declare(strict_types=1);

namespace App\Application\Project\GetProjectPage;

final readonly class FinancialModelListItem
{
    public function __construct(
        public string $shortId,
        public string $title,
        public int $versionNumber,
        public string $status,
        public bool $isArchived,
    ) {
    }
}
