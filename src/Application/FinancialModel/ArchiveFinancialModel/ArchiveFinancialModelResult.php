<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\ArchiveFinancialModel;

final readonly class ArchiveFinancialModelResult
{
    public function __construct(
        public string $financialModelShortId,
        public string $status,
        public bool $isArchived,
        public ?string $archivedAt,
    ) {
    }
}
