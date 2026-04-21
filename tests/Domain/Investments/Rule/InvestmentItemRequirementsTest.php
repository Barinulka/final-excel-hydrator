<?php

declare(strict_types=1);

namespace App\Tests\Domain\Investments\Rule;

use App\Domain\Investments\Enum\InvestmentCategory;
use App\Domain\Investments\Rule\InvestmentItemInput;
use App\Domain\Investments\Rule\InvestmentItemRequirements;
use PHPUnit\Framework\TestCase;

final class InvestmentItemRequirementsTest extends TestCase
{
    public function testLandRequiresCadastralValueWhenLandTaxable(): void
    {
        $requiredFields = InvestmentItemRequirements::requiredFields(new InvestmentItemInput(
            category: InvestmentCategory::Land,
            landTaxable: true,
        ));

        $this->assertContainsFields($requiredFields, [
            'initialCost',
            'title',
            'landTaxable',
            'cadastralValue',
        ]);

        $this->assertDoesNotContainField($requiredFields, 'vatApplied');
    }

    public function testBuildingsRequirePropertyTaxBaseWhenPropertyTaxable(): void
    {
        $requiredFields = InvestmentItemRequirements::requiredFields(new InvestmentItemInput(
            category: InvestmentCategory::Buildings,
            propertyTaxable: true,
            propertyTaxBaseCadastral: false,
        ));

        $this->assertContainsFields($requiredFields, [
            'initialCost',
            'title',
            'vatApplied',
            'propertyTaxable',
            'propertyTaxBase',
            'usefulLifeYears',
        ]);

        $this->assertDoesNotContainField($requiredFields, 'vehiclePowerHp');
    }

    public function testBuildingsRequireCadastralValueWhenPropertyTaxBaseIsCadastral(): void
    {
        $requiredFields = InvestmentItemRequirements::requiredFields(new InvestmentItemInput(
            category: InvestmentCategory::Buildings,
            propertyTaxable: true,
            propertyTaxBaseCadastral: true,
        ));

        $this->assertContainsField($requiredFields, 'cadastralValue');
    }

    public function testVehiclesRequireVehiclePowerWhenVehicleTaxable(): void
    {
        $requiredFields = InvestmentItemRequirements::requiredFields(new InvestmentItemInput(
            category: InvestmentCategory::Vehicles,
            vehicleTaxable: true,
        ));

        $this->assertContainsFields($requiredFields, [
            'vehicleTaxable',
            'vehiclePowerHp',
            'usefulLifeYears',
        ]);
    }

    public function testOtherCapitalizedRequiresUsefulLifeOnlyWhenDepreciationEnabled(): void
    {
        $requiredFieldsWithoutDepreciation = InvestmentItemRequirements::requiredFields(new InvestmentItemInput(
            category: InvestmentCategory::OtherCapitalized,
            depreciationEnabled: false,
        ));

        $this->assertContainsField($requiredFieldsWithoutDepreciation, 'depreciationEnabled');
        $this->assertDoesNotContainField($requiredFieldsWithoutDepreciation, 'usefulLifeYears');

        $requiredFieldsWithDepreciation = InvestmentItemRequirements::requiredFields(new InvestmentItemInput(
            category: InvestmentCategory::OtherCapitalized,
            depreciationEnabled: true,
        ));

        $this->assertContainsFields($requiredFieldsWithDepreciation, [
            'depreciationEnabled',
            'usefulLifeYears',
        ]);
    }

    public function testDeferredMarketingRequiresWriteOffYears(): void
    {
        $requiredFields = InvestmentItemRequirements::requiredFields(new InvestmentItemInput(
            category: InvestmentCategory::DeferredMarketingAndTraining,
        ));

        $this->assertContainsFields($requiredFields, [
            'initialCost',
            'vatApplied',
            'deferredExpenseWriteOffYears',
        ]);
    }

    /**
     * @param string[] $actualFields
     * @param string[] $expectedFields
     */
    private function assertContainsFields(array $actualFields, array $expectedFields): void
    {
        foreach ($expectedFields as $expectedField) {
            $this->assertContainsField($actualFields, $expectedField);
        }
    }

    /**
     * @param string[] $actualFields
     */
    private function assertContainsField(array $actualFields, string $expectedField): void
    {
        self::assertContains($expectedField, $actualFields);
    }

    /**
     * @param string[] $actualFields
     */
    private function assertDoesNotContainField(array $actualFields, string $unexpectedField): void
    {
        self::assertNotContains($unexpectedField, $actualFields);
    }
}
