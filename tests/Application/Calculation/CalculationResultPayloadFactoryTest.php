<?php

declare(strict_types=1);

namespace App\Tests\Application\Calculation;

use App\Application\Calculation\CalculationResult;
use App\Application\Calculation\CalculationResultPayloadFactory;
use App\Application\Calculation\CalculationRow;
use App\Application\Calculation\CalculationTable;
use PHPUnit\Framework\TestCase;

final class CalculationResultPayloadFactoryTest extends TestCase
{
    public function testCreatesPayloadForSnapshotStorage(): void
    {
        $factory = new CalculationResultPayloadFactory();

        $payload = $factory->create(new CalculationResult(
            tables: [
                new CalculationTable(
                    code: 'timeline',
                    title: 'Временная шкала',
                    periods: ['2026-04', '2026-05'],
                    rows: [
                        new CalculationRow(
                            code: 'investment_activity',
                            title: 'Инвестиционная деятельность',
                            values: [1, 0],
                        ),
                    ],
                ),
            ],
            metrics: [
                'period_count' => 2,
            ],
            warnings: [
                'Нет данных по выручке.',
            ],
        ));

        self::assertSame([
            'tables' => [
                [
                    'code' => 'timeline',
                    'title' => 'Временная шкала',
                    'periods' => ['2026-04', '2026-05'],
                    'rows' => [
                        [
                            'code' => 'investment_activity',
                            'title' => 'Инвестиционная деятельность',
                            'values' => [1, 0],
                        ],
                    ],
                ],
            ],
            'metrics' => [
                'period_count' => 2,
            ],
            'warnings' => [
                'Нет данных по выручке.',
            ],
        ], $payload);
    }
}
