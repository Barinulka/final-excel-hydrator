<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Api\Response\FinancialModel;

use App\Application\FinancialModel\BuildFinancialModelSummary\BuildFinancialModelSummaryResult;
use App\Application\FinancialModel\BuildFinancialModelSummary\TimelinePeriodSummary;
use App\Application\FinancialModel\BuildFinancialModelSummary\TimelineSummary;
use App\Application\FinancialModel\BuildFinancialModelSummary\TimeParamsSummary;
use App\Presentation\Api\Response\FinancialModel\FinancialModelSummaryApiResponseFactory;
use PHPUnit\Framework\TestCase;

final class FinancialModelSummaryApiResponseFactoryTest extends TestCase
{
    public function testCreatesFinancialModelSummaryApiResponse(): void
    {
        $factory = new FinancialModelSummaryApiResponseFactory();

        $response = $factory->create(new BuildFinancialModelSummaryResult(
            projectShortId: '23456789ab',
            projectTitle: 'Test Project',
            financialModelShortId: 'ab23456789',
            financialModelTitle: 'Test Project v1',
            financialModelStatus: 'active',
            isFinancialModelArchived: false,
            amountDisplayFormat: 'wholeRubles',
            timeParams: new TimeParamsSummary(
                investmentStartMonth: '2026-04',
                investmentDurationMonths: 6,
                commercialOperationDurationMonths: 24,
                totalDurationMonths: 30,
                forecastStep: 'quarter',
                forecastStepLabel: 'кв.',
            ),
            timeline: new TimelineSummary(
                investmentStartDate: '2026-04-01',
                investmentEndDate: '2026-09-30',
                commercialOperationStartDate: '2026-10-01',
                commercialOperationEndDate: '2028-09-30',
                modelStartDate: '2026-04-01',
                modelEndDate: '2028-09-30',
                periodCount: 30,
                periods: [
                    new TimelinePeriodSummary(
                        periodNumber: 1,
                        yearMonth: '2026-04',
                        periodStartDate: '2026-04-01',
                        periodEndDate: '2026-04-30',
                        investmentActivity: true,
                        operatingActivity: false,
                        operatingStart: false,
                    ),
                    new TimelinePeriodSummary(
                        periodNumber: 7,
                        yearMonth: '2026-10',
                        periodStartDate: '2026-10-01',
                        periodEndDate: '2026-10-31',
                        investmentActivity: false,
                        operatingActivity: true,
                        operatingStart: true,
                    ),
                ],
            ),
            warnings: ['Инвестиционный блок не заполнен.'],
        ));

        self::assertSame([
            'data' => [
                'financialModel' => [
                    'shortId' => 'ab23456789',
                    'title' => 'Test Project v1',
                    'status' => 'active',
                    'isArchived' => false,
                    'amountDisplayFormat' => 'wholeRubles',
                ],
                'timeParams' => [
                    'investmentStartMonth' => '2026-04',
                    'investmentDurationMonths' => 6,
                    'commercialOperationDurationMonths' => 24,
                    'totalDurationMonths' => 30,
                    'forecastStep' => 'quarter',
                    'forecastStepLabel' => 'кв.',
                ],
                'timeline' => [
                    'investmentStartDate' => '2026-04-01',
                    'investmentEndDate' => '2026-09-30',
                    'commercialOperationStartDate' => '2026-10-01',
                    'commercialOperationEndDate' => '2028-09-30',
                    'modelStartDate' => '2026-04-01',
                    'modelEndDate' => '2028-09-30',
                    'periodCount' => 30,
                    'periods' => [
                        [
                            'periodNumber' => 1,
                            'yearMonth' => '2026-04',
                            'periodStartDate' => '2026-04-01',
                            'periodEndDate' => '2026-04-30',
                            'investmentActivity' => true,
                            'operatingActivity' => false,
                            'operatingStart' => false,
                        ],
                        [
                            'periodNumber' => 7,
                            'yearMonth' => '2026-10',
                            'periodStartDate' => '2026-10-01',
                            'periodEndDate' => '2026-10-31',
                            'investmentActivity' => false,
                            'operatingActivity' => true,
                            'operatingStart' => true,
                        ],
                    ],
                ],
                'warnings' => [
                    'Инвестиционный блок не заполнен.',
                ],
            ],
        ], $response);
    }
}
