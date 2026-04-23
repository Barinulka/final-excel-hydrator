<?php

declare(strict_types=1);

namespace App\Domain\Investments\Enum;

enum PropertyTaxBase: string
{
    case CadastralValue = 'cadastralValue';
    case AverageAnnualValue = 'averageAnnualValue';

    public function label(): string
    {
        return match ($this) {
            self::CadastralValue => 'Кадастровая стоимость',
            self::AverageAnnualValue => 'Среднегодовая стоимость',
        };
    }
}
