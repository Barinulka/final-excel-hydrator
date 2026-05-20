<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Domain\Investments\Enum\InvestmentCategory;
use App\Domain\Investments\Enum\PropertyTaxBase;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\Money;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\FinancialModel;
use App\Entity\InvestmentBlock;
use App\Entity\InvestmentItem;
use App\Entity\Project;
use App\Entity\TimeParams;
use App\Entity\User;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class InvestmentItemTest extends TestCase
{
    public function testItCreatesInvestmentItem(): void
    {
        $investmentBlock = $this->createInvestmentBlock();

        $item = InvestmentItem::create(
            investmentBlock: $investmentBlock,
            category: InvestmentCategory::Equipment,
            initialCost: Money::fromString('1250000.50'),
            vatApplied: true,
            title: 'Погрузчик',
        );

        self::assertSame($investmentBlock, $item->getInvestmentBlock());
        self::assertSame(InvestmentCategory::Equipment, $item->getCategory());
        self::assertSame('1250000.50', $item->getInitialCost()->toString());
        self::assertTrue($item->isVatApplied());
        self::assertSame('Погрузчик', $item->getTitle());
        self::assertCount(1, $investmentBlock->getItems());
        self::assertTrue($investmentBlock->getItems()->contains($item));
    }

    public function testItNormalizesBlankTitleToNull(): void
    {
        $item = InvestmentItem::create(
            investmentBlock: $this->createInvestmentBlock(),
            category: InvestmentCategory::Equipment,
            initialCost: Money::fromString('50000'),
            vatApplied: true,
            title: '   ',
        );

        self::assertNull($item->getTitle());
    }

    public function testItAllowsRenamingToNull(): void
    {
        $item = InvestmentItem::create(
            investmentBlock: $this->createInvestmentBlock(),
            category: InvestmentCategory::Equipment,
            initialCost: Money::fromString('50000'),
            vatApplied: true,
            title: 'Старое название',
        );

        $item->rename(null);

        self::assertNull($item->getTitle());
    }

    public function testItChangesInitialCost(): void
    {
        $item = InvestmentItem::create(
            investmentBlock: $this->createInvestmentBlock(),
            category: InvestmentCategory::Equipment,
            initialCost: Money::fromString('50000'),
            vatApplied: true,
        );

        $item->changeInitialCost(Money::fromString('75000.25'));

        self::assertSame('75000.25', $item->getInitialCost()->toString());
    }

    public function testItChangesVatApplied(): void
    {
        $item = InvestmentItem::create(
            investmentBlock: $this->createInvestmentBlock(),
            category: InvestmentCategory::Equipment,
            initialCost: Money::fromString('50000'),
            vatApplied: true,
        );

        $item->changeVatApplied(false);

        self::assertFalse($item->isVatApplied());
    }

    public function testItChangesOptionalDomainFields(): void
    {
        $item = InvestmentItem::create(
            investmentBlock: $this->createInvestmentBlock(),
            category: InvestmentCategory::Equipment,
            initialCost: Money::fromString('50000'),
            vatApplied: true,
        );

        $item
            ->changeUsefulLifeYears(5)
            ->changeDeferredExpenseWriteOffYears(3)
            ->changeLeaseTermMonths(24)
            ->changeDepreciationEnabled(true);

        self::assertSame(5, $item->getUsefulLifeYears());
        self::assertSame(3, $item->getDeferredExpenseWriteOffYears());
        self::assertSame(24, $item->getLeaseTermMonths());
        self::assertTrue($item->isDepreciationEnabled());
    }

    public function testItAllowsNullForOptionalDomainFields(): void
    {
        $item = InvestmentItem::create(
            investmentBlock: $this->createInvestmentBlock(),
            category: InvestmentCategory::Equipment,
            initialCost: Money::fromString('50000'),
            vatApplied: true,
        );

        $item
            ->changeUsefulLifeYears(null)
            ->changeDeferredExpenseWriteOffYears(null)
            ->changeLeaseTermMonths(null)
            ->changeDepreciationEnabled(null);

        self::assertNull($item->getUsefulLifeYears());
        self::assertNull($item->getDeferredExpenseWriteOffYears());
        self::assertNull($item->getLeaseTermMonths());
        self::assertNull($item->isDepreciationEnabled());
    }

    public function testItRejectsNonPositiveUsefulLifeYears(): void
    {
        $item = InvestmentItem::create(
            investmentBlock: $this->createInvestmentBlock(),
            category: InvestmentCategory::Equipment,
            initialCost: Money::fromString('50000'),
            vatApplied: true,
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Срок полезного использования должен быть положительным числом.');

        $item->changeUsefulLifeYears(0);
    }

    public function testItRejectsNonPositiveDeferredExpenseWriteOffYears(): void
    {
        $item = InvestmentItem::create(
            investmentBlock: $this->createInvestmentBlock(),
            category: InvestmentCategory::DeferredMarketingAndTraining,
            initialCost: Money::fromString('50000'),
            vatApplied: true,
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Срок списания расходов будущих периодов должен быть положительным числом.');

        $item->changeDeferredExpenseWriteOffYears(0);
    }

    public function testItRejectsNonPositiveLeaseTermMonths(): void
    {
        $item = InvestmentItem::create(
            investmentBlock: $this->createInvestmentBlock(),
            category: InvestmentCategory::LeaseholdImprovements,
            initialCost: Money::fromString('50000'),
            vatApplied: true,
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Срок договора аренды должен быть положительным числом.');

        $item->changeLeaseTermMonths(-1);
    }

    public function testItChangesTaxFields(): void
    {
        $item = InvestmentItem::create(
            investmentBlock: $this->createInvestmentBlock(),
            category: InvestmentCategory::Buildings,
            initialCost: Money::fromString('50000'),
            vatApplied: true,
        );

        $item
            ->changeLandTaxable(true)
            ->changePropertyTaxable(true)
            ->changePropertyTaxBase(PropertyTaxBase::CadastralValue)
            ->changeCadastralValue(Money::fromString('2500000'));

        self::assertTrue($item->isLandTaxable());
        self::assertTrue($item->isPropertyTaxable());
        self::assertSame(PropertyTaxBase::CadastralValue, $item->getPropertyTaxBase());
        self::assertSame('2500000.00', $item->getCadastralValue()?->toString());
    }

    public function testItAllowsNullForTaxFields(): void
    {
        $item = InvestmentItem::create(
            investmentBlock: $this->createInvestmentBlock(),
            category: InvestmentCategory::Land,
            initialCost: Money::fromString('50000'),
            vatApplied: false,
        );

        $item
            ->changeLandTaxable(null)
            ->changePropertyTaxable(null)
            ->changePropertyTaxBase(null)
            ->changeCadastralValue(null);

        self::assertNull($item->isLandTaxable());
        self::assertNull($item->isPropertyTaxable());
        self::assertNull($item->getPropertyTaxBase());
        self::assertNull($item->getCadastralValue());
    }

    public function testItChangesVehicleTaxFields(): void
    {
        $item = InvestmentItem::create(
            investmentBlock: $this->createInvestmentBlock(),
            category: InvestmentCategory::Vehicles,
            initialCost: Money::fromString('50000'),
            vatApplied: true,
        );

        $item
            ->changeVehicleTaxable(true)
            ->changeVehiclePowerHp(180);

        self::assertTrue($item->isVehicleTaxable());
        self::assertSame(180, $item->getVehiclePowerHp());
    }

    public function testItAllowsNullForVehicleTaxFields(): void
    {
        $item = InvestmentItem::create(
            investmentBlock: $this->createInvestmentBlock(),
            category: InvestmentCategory::Vehicles,
            initialCost: Money::fromString('50000'),
            vatApplied: true,
        );

        $item
            ->changeVehicleTaxable(null)
            ->changeVehiclePowerHp(null);

        self::assertNull($item->isVehicleTaxable());
        self::assertNull($item->getVehiclePowerHp());
    }

    public function testItRejectsNonPositiveVehiclePowerHp(): void
    {
        $item = InvestmentItem::create(
            investmentBlock: $this->createInvestmentBlock(),
            category: InvestmentCategory::Vehicles,
            initialCost: Money::fromString('50000'),
            vatApplied: true,
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Мощность транспортного средства должна быть положительным числом.');

        $item->changeVehiclePowerHp(0);
    }

    private function createInvestmentBlock(): InvestmentBlock
    {
        $user = (new User())
            ->setEmail('investments@example.com')
            ->setPassword('hashed-password');

        $project = Project::create(
            owner: $user,
            shortId: ShortId::fromString('abcdefghjk'),
            title: 'Проект',
            description: null,
        );

        $timeParams = TimeParams::create(
            investmentStartMonth: YearMonth::fromString('2026-04'),
            investmentDuration: MonthDuration::fromInt(6),
            commercialOperationDuration: MonthDuration::fromInt(24),
            forecastStep: ForecastStep::Month,
        );

        $financialModel = FinancialModel::create(
            project: $project,
            owner: $project->getOwner(),
            shortId: ShortId::fromString('mnpqrstuvw'),
            title: 'Модель',
            description: null,
            versionNumber: 1,
            timeParams: $timeParams,
        );

        return new InvestmentBlock($financialModel);
    }
}
