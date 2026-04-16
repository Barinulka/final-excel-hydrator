<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\CreateFinancialModel;

final readonly class CreateFinancialModelResult
{
    public function __construct(
        public string $projectShortId,
        public string $financialModelShortId,
        public string $title,
        public int $versionNumber,
    ) {
    }
}
