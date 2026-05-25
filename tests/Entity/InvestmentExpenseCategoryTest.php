<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Domain\Investments\Enum\InvestmentExpenseCategoryType;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\FinancialModel;
use App\Entity\InvestmentBlock;
use App\Entity\InvestmentExpenseCategory;
use App\Entity\TimeParams;
use App\Entity\User;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class InvestmentExpenseCategoryTest extends TestCase
{
    public function testItCreatesCategoryFromPredefinedType(): void
    {
        $investmentBlock = $this->createInvestmentBlock();

        $category = InvestmentExpenseCategory::fromPredefinedType(
            investmentBlock: $investmentBlock,
            type: InvestmentExpenseCategoryType::Equipment,
            relatedExpenses: ' Доставка и монтаж ',
        );

        self::assertSame($investmentBlock, $category->getInvestmentBlock());
        self::assertSame(InvestmentExpenseCategoryType::Equipment, $category->getType());
        self::assertNull($category->getCustomTitle());
        self::assertSame('Оборудование', $category->getTitle());
        self::assertSame('Доставка и монтаж', $category->getRelatedExpenses());
        self::assertCount(1, $investmentBlock->getExpenseCategories());
        self::assertTrue($investmentBlock->getExpenseCategories()->contains($category));
    }

    public function testItCreatesCategoryFromCustomTitle(): void
    {
        $investmentBlock = $this->createInvestmentBlock();

        $category = InvestmentExpenseCategory::fromCustomTitle(
            investmentBlock: $investmentBlock,
            customTitle: '  Кухонная линия  ',
            relatedExpenses: ' Проектирование ',
        );

        self::assertSame($investmentBlock, $category->getInvestmentBlock());
        self::assertNull($category->getType());
        self::assertSame('Кухонная линия', $category->getCustomTitle());
        self::assertSame('Кухонная линия', $category->getTitle());
        self::assertSame('Проектирование', $category->getRelatedExpenses());
        self::assertCount(1, $investmentBlock->getExpenseCategories());
        self::assertTrue($investmentBlock->getExpenseCategories()->contains($category));
    }

    public function testItRejectsBlankCustomTitle(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Название категории инвестиций не должно быть пустым.');

        InvestmentExpenseCategory::fromCustomTitle(
            investmentBlock: $this->createInvestmentBlock(),
            customTitle: '   ',
            relatedExpenses: null,
        );
    }

    public function testItNormalizesBlankRelatedExpensesToNull(): void
    {
        $category = InvestmentExpenseCategory::fromPredefinedType(
            investmentBlock: $this->createInvestmentBlock(),
            type: InvestmentExpenseCategoryType::Renovation,
            relatedExpenses: ' Демонтаж ',
        );

        $category->changeRelatedExpenses('   ');

        self::assertNull($category->getRelatedExpenses());
    }

    private function createInvestmentBlock(): InvestmentBlock
    {
        $user = (new User())
            ->setEmail('investment-categories@example.com')
            ->setPassword('hashed-password');
        $timeParams = TimeParams::create(
            investmentStartMonth: YearMonth::fromString('2026-04'),
            investmentDuration: MonthDuration::fromInt(6),
            commercialOperationDuration: MonthDuration::fromInt(24),
            forecastStep: ForecastStep::Month,
        );

        $financialModel = FinancialModel::create(
            owner: $user,
            shortId: ShortId::fromString('mnpqrstuvw'),
            title: 'Модель',
            description: null,
            versionNumber: 1,
            timeParams: $timeParams,
        );

        return new InvestmentBlock($financialModel);
    }
}
