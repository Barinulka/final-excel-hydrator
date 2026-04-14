<?php

namespace App\Domain\FinancialModel\Enum;

enum FinancialModelStatus: string
{
    case Active = 'active';
    case Archived = 'archived';
}
