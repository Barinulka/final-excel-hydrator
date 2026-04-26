<?php

declare(strict_types=1);

namespace App\Tests\Application\Calculation;

use App\Application\Calculation\CalculationResult;
use App\Application\Calculation\CalculationRow;
use App\Application\Calculation\CalculationTable;
use PHPUnit\Framework\TestCase;

final class CalculationResultTest extends TestCase
{
    public function testCreatesCalculationResultWithTablesMetricsAndWarnings(): void
    {
        $row = new CalculationRow(
            code: 'total_revenue',
            title: 'Итого выручка',
            values: [100000, 120000, 140000],
        );

        $table = new CalculationTable(
            code: 'revenue',
            title: 'Выручка',
            periods: ['2026-01', '2026-02', '2026-03'],
            rows: [$row],
        );

        $result = new CalculationResult(
            tables: [$table],
            metrics: [
                'gross_margin' => 0.65,
                'ebitda' => 50000,
            ],
            warnings: [
                'Нет данных по себестоимости.',
            ],
        );

        self::assertSame([$table], $result->tables);
        self::assertSame(['2026-01', '2026-02', '2026-03'], $result->tables[0]->periods);
        self::assertSame([$row], $result->tables[0]->rows);
        self::assertSame([100000, 120000, 140000], $result->tables[0]->rows[0]->values);
        self::assertSame(0.65, $result->metrics['gross_margin']);
        self::assertSame(50000, $result->metrics['ebitda']);
        self::assertSame(['Нет данных по себестоимости.'], $result->warnings);
    }
}
