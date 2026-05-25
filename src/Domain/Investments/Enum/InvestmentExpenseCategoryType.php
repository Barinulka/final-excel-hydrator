<?php

namespace App\Domain\Investments\Enum;

enum InvestmentExpenseCategoryType: string
{
    case Equipment = 'equipment';
    case FurnitureAndOfficeEquipment = 'furnitureAndOfficeEquipment';
    case Renovation = 'renovation';
    case SoftwareWebsiteLicenses = 'softwareWebsiteLicenses';
    case Transport = 'transport';
    case IntangibleAssetsAndPatents = 'intangibleAssetsAndPatents';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Equipment => 'Оборудование',
            self::FurnitureAndOfficeEquipment => 'Мебель и оргтехника',
            self::Renovation => 'Ремонт помещения',
            self::SoftwareWebsiteLicenses => 'ПО, сайт, лицензии',
            self::Transport => 'Транспорт',
            self::IntangibleAssetsAndPatents => 'НМА и патенты',
            self::Other => 'Прочее',
        };
    }
}
