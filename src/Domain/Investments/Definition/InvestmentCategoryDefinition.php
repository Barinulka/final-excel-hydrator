<?php

declare(strict_types=1);

namespace App\Domain\Investments\Definition;

use App\Domain\Investments\Enum\InvestmentCategory;

final readonly class InvestmentCategoryDefinition
{
    public function __construct(
        private InvestmentCategory $category,
        private bool $titleRequired,
        private bool $vatApplicable,
        private bool $landTaxApplicable,
        private bool $propertyTaxApplicable,
        private bool $propertyTaxBaseApplicable,
        private bool $cadastralValueApplicable,
        private bool $vehicleTaxApplicable,
        private bool $vehiclePowerApplicable,
        private bool $usefulLifeYearsApplicable,
        private bool $deferredExpenseWriteOffYearsApplicable,
        private bool $leaseTermMonthsApplicable,
        private bool $depreciationChoiceApplicable,
    ) {
    }

    public function getCategory(): InvestmentCategory
    {
        return $this->category;
    }

    public function isTitleRequired(): bool
    {
        return $this->titleRequired;
    }

    public function isVatApplicable(): bool
    {
        return $this->vatApplicable;
    }

    public function isLandTaxApplicable(): bool
    {
        return $this->landTaxApplicable;
    }

    public function isPropertyTaxApplicable(): bool
    {
        return $this->propertyTaxApplicable;
    }

    public function isPropertyTaxBaseApplicable(): bool
    {
        return $this->propertyTaxBaseApplicable;
    }

    public function isCadastralValueApplicable(): bool
    {
        return $this->cadastralValueApplicable;
    }

    public function isVehicleTaxApplicable(): bool
    {
        return $this->vehicleTaxApplicable;
    }

    public function isVehiclePowerApplicable(): bool
    {
        return $this->vehiclePowerApplicable;
    }

    public function isUsefulLifeYearsApplicable(): bool
    {
        return $this->usefulLifeYearsApplicable;
    }

    public function isDeferredExpenseWriteOffYearsApplicable(): bool
    {
        return $this->deferredExpenseWriteOffYearsApplicable;
    }

    public function isLeaseTermMonthsApplicable(): bool
    {
        return $this->leaseTermMonthsApplicable;
    }

    public function isDepreciationChoiceApplicable(): bool
    {
        return $this->depreciationChoiceApplicable;
    }
}
