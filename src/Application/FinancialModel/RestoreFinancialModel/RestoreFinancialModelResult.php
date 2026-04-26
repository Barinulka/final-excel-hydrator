<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\RestoreFinancialModel;

final readonly class RestoreFinancialModelResult
{
    public function __construct(
        public string $projectShortId,
        public string $financialModelShortId,
        public string $status,
        public bool $isArchived,
        public ?string $archivedAt,
    ) {
    }
}
