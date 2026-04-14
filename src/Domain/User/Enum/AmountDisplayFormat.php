<?php

namespace App\Domain\User\Enum;

enum AmountDisplayFormat: string
{
    case WholeRubles = 'wholeRubles';
    case DecimalRubles = 'decimalRubles';
}
