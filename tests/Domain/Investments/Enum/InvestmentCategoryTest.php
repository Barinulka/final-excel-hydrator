<?php

declare(strict_types=1);

namespace App\Tests\Domain\Investments\Enum;

use App\Domain\Investments\Enum\InvestmentCategory;
use App\Domain\Investments\Enum\InvestmentGroup;
use App\Domain\Investments\Enum\InvestmentTreatment;
use PHPUnit\Framework\TestCase;

final class InvestmentCategoryTest extends TestCase
{
    public function testLandCategoryMapping(): void
    {
        $category = InvestmentCategory::Land;

        self::assertSame(InvestmentGroup::Capitalized, $category->group());
        self::assertSame(InvestmentTreatment::CapexNonDepreciable, $category->treatment());
        self::assertSame('Земельный участок', $category->label());
    }

    public function testInitialInventoryCategoryMapping(): void
    {
        $category = InvestmentCategory::InitialInventory;

        self::assertSame(InvestmentGroup::NonCapitalized, $category->group());
        self::assertSame(InvestmentTreatment::InitialWorkingCapital, $category->treatment());
        self::assertSame('Первоначальные запасы', $category->label());
    }

    public function testDeferredMarketingCategoryMapping(): void
    {
        $category = InvestmentCategory::DeferredMarketingAndTraining;

        self::assertSame(InvestmentGroup::NonCapitalized, $category->group());
        self::assertSame(InvestmentTreatment::DeferredExpense, $category->treatment());
        self::assertSame('Расходы на рекламу, маркетинг, обучение и др.', $category->label());
    }

    public function testOtherCapitalizedCategoryMapping(): void
    {
        $category = InvestmentCategory::OtherCapitalized;

        self::assertSame(InvestmentGroup::Capitalized, $category->group());
        self::assertSame(InvestmentTreatment::CapexDepreciable, $category->treatment());
        self::assertSame('Другое', $category->label());
    }
}
