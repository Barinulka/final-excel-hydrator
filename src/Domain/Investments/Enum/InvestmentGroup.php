<?php

namespace App\Domain\Investments\Enum;

enum InvestmentGroup: string
{
    case Capitalized = 'capitalized';
    case NonCapitalized = 'nonCapitalized';

    public function label(): string
    {
        return match ($this) {
            self::Capitalized => 'Капитализируемые затраты',
            self::NonCapitalized => 'Некапитализируемые затраты',
        };
    }
}
