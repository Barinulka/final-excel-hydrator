<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\UpdateFinancialModelDetails;

final readonly class UpdateFinancialModelDetailsResult
{
    public function __construct(
        public string $financialModelShortId,
        public string $title,
        public ?string $description,
    ) {
    }
}
