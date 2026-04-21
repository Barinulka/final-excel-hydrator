<?php

namespace App\Domain\Investments\Enum;

enum InvestmentTreatment: string
{
    case CapexNonDepreciable = 'capexNonDepreciable';
    case CapexDepreciable = 'capexDepreciable';
    case DeferredExpense = 'deferredExpense';
    case InitialWorkingCapital = 'initialWorkingCapital';

    public function label(): string
    {
        return match ($this) {
            self::CapexNonDepreciable => 'Кап. вложения без амортизации',
            self::CapexDepreciable => 'Кап. вложения с амортизацией',
            self::DeferredExpense => 'Расходы будущих периодов',
            self::InitialWorkingCapital => 'Первоначальный оборотный капитал',
        };
    }
}
