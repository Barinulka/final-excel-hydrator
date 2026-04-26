<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Api\Response\Calculation;

use App\Application\Calculation\CalculationResult;
use App\Application\Calculation\CalculationRow;
use App\Application\Calculation\CalculationTable;
use App\Presentation\Api\Response\Calculation\CalculationResultApiResponseFactory;
use PHPUnit\Framework\TestCase;

final class CalculationResultApiResponseFactoryTest extends TestCase
{
    public function testCreatesCalculationResultApiResponse(): void
    {
        $factory = new CalculationResultApiResponseFactory();

        $response = $factory->create(new CalculationResult(
            tables: [
                new CalculationTable(
                    code: 'timeline',
                    title: 'Временная шкала',
                    periods: ['2026-01', '2026-02'],
                    rows: [
                        new CalculationRow(
                            code: 'period_start_date',
                            title: 'Начало месяца',
                            values: ['2026-01-01', '2026-02-01'],
                        ),
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
            warnings: [],
        ));

        self::assertSame([
            'data' => [
                'tables' => [
                    [
                        'code' => 'timeline',
                        'title' => 'Временная шкала',
                        'periods' => ['2026-01', '2026-02'],
                        'rows' => [
                            [
                                'code' => 'period_start_date',
                                'title' => 'Начало месяца',
                                'values' => ['2026-01-01', '2026-02-01'],
                            ],
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
                'warnings' => [],
            ],
        ], $response);
    }
}
