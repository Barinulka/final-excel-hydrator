<?php

namespace App\Domain\FinancialModel\Enum;

enum AmountDisplayFormat: string
{
    case WholeRubles = 'wholeRubles';
    case DecimalRubles = 'decimalRubles';
}
