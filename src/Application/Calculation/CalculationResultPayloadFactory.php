<?php

declare(strict_types=1);

namespace App\Application\Calculation;

final readonly class CalculationResultPayloadFactory
{
    public function create(CalculationResult $result): array
    {
        return [
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
        ];
    }
}
