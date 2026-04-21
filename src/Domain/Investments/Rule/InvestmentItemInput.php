<?php

declare(strict_types=1);

namespace App\Domain\Investments\Rule;

use App\Domain\Investments\Enum\InvestmentCategory;

final readonly class InvestmentItemInput
{
    public function __construct(
        public InvestmentCategory $category,
        public ?bool $landTaxable = null,
        public ?bool $propertyTaxable = null,
        public ?bool $propertyTaxBaseCadastral = null,
        public ?bool $vehicleTaxable = null,
        public ?bool $depreciationEnabled = null,
    ) {
    }
}
