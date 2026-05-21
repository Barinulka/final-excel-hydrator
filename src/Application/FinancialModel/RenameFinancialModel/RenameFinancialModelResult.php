<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\RenameFinancialModel;

final readonly class RenameFinancialModelResult
{
    public function __construct(
        public string $financialModelShortId,
        public string $title,
    ) {
    }
}
