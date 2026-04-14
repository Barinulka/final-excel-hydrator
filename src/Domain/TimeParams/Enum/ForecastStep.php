<?php

namespace App\Domain\TimeParams\Enum;

enum ForecastStep: string
{
    case Month = 'month';
    case Quarter = 'quarter';
    case Year = 'year';
}
