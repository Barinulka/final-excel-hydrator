<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\DeleteFinancialModel;

final readonly class DeleteFinancialModelResult
{
    public function __construct(
        public string $projectShortId,
        public string $financialModelShortId,
        public bool $isDeleted,
    ) {
    }
}
