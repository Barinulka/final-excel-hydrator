<?php

declare(strict_types=1);

namespace App\Domain\Investments\Rule;

use App\Domain\Investments\Definition\InvestmentCategoryDefinitions;
use App\Domain\Investments\Enum\InvestmentCategory;

final readonly class InvestmentItemRequirements
{
    /**
     * @return string[]
     */
    public static function requiredFields(InvestmentItemInput $input): array
    {
        $definition = InvestmentCategoryDefinitions::for($input->category);

        $requiredFields = ['initialCost'];

        if ($definition->isTitleRequired()) {
            self::addField($requiredFields, 'title');
        }

        if ($definition->isVatApplicable()) {
            self::addField($requiredFields, 'vatApplied');
        }

        if ($definition->isLandTaxApplicable()) {
            self::addField($requiredFields, 'landTaxable');
        }

        if ($definition->isPropertyTaxApplicable()) {
            self::addField($requiredFields, 'propertyTaxable');
        }

        if ($definition->isVehicleTaxApplicable()) {
            self::addField($requiredFields, 'vehicleTaxable');
        }

        if (
            $definition->isUsefulLifeYearsApplicable()
            && InvestmentCategory::OtherCapitalized !== $input->category
        ) {
            self::addField($requiredFields, 'usefulLifeYears');
        }

        if ($definition->isDeferredExpenseWriteOffYearsApplicable()) {
            self::addField($requiredFields, 'deferredExpenseWriteOffYears');
        }

        if ($definition->isLeaseTermMonthsApplicable()) {
            self::addField($requiredFields, 'leaseTermMonths');
        }

        if ($definition->isDepreciationChoiceApplicable()) {
            self::addField($requiredFields, 'depreciationEnabled');
        }

        if (InvestmentCategory::Land === $input->category && true === $input->landTaxable) {
            self::addField($requiredFields, 'cadastralValue');
        }

        if (InvestmentCategory::Buildings === $input->category && true === $input->propertyTaxable) {
            self::addField($requiredFields, 'propertyTaxBase');
        }

        if (
            InvestmentCategory::Buildings === $input->category
            && true === $input->propertyTaxable
            && true === $input->propertyTaxBaseCadastral
        ) {
            self::addField($requiredFields, 'cadastralValue');
        }

        if (InvestmentCategory::Vehicles === $input->category && true === $input->vehicleTaxable) {
            self::addField($requiredFields, 'vehiclePowerHp');
        }

        if (
            InvestmentCategory::OtherCapitalized === $input->category
            && true === $input->depreciationEnabled
        ) {
            self::addField($requiredFields, 'usefulLifeYears');
        }

        return $requiredFields;
    }

    /**
     * @param string[] $requiredFields
     */
    private static function addField(array &$requiredFields, string $field): void
    {
        if (!in_array($field, $requiredFields, true)) {
            $requiredFields[] = $field;
        }
    }
}
