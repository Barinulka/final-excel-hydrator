<?php

declare(strict_types=1);

namespace App\Presentation\Api\Response\Calculation;

use App\Application\Calculation\CalculationResult;
use App\Application\Calculation\CalculationRow;
use App\Application\Calculation\CalculationTable;

final readonly class CalculationResultApiResponseFactory
{
    public function create(CalculationResult $result): array
    {
        return [
            'data' => [
                'tables' => array_map(
                    static fn (CalculationTable $table): array => [
                        'code' => $table->code,
                        'title' => $table->title,
                        'periods' => $table->periods,
                        'rows' => array_map(
                            static fn (CalculationRow $row): array => [
                                'code' => $row->code,
                                'title' => $row->title,
                                'values' => $row->values,
                            ],
                            $table->rows,
                        ),
                    ],
                    $result->tables,
                ),
                'metrics' => $result->metrics,
                'warnings' => $result->warnings,
            ],
        ];
    }
}
