<?php

namespace App\Domain\Investments\Enum;

enum InvestmentCategory: string
{
    case Land = 'land';
    case Buildings = 'buildings';
    case Equipment = 'equipment';
    case FurnitureAndOfficeEquipment = 'furnitureAndOfficeEquipment';
    case Vehicles = 'vehicles';
    case IntangibleAssets = 'intangibleAssets';
    case LeaseholdImprovements = 'leaseholdImprovements';
    case OtherCapitalized = 'otherCapitalized';
    case InitialInventory = 'initialInventory';
    case DeferredMarketingAndTraining = 'deferredMarketingAndTraining';
    case OtherDeferredExpense = 'otherDeferredExpense';

    public function label(): string
    {
        return match ($this) {
            self::Land => 'Земельный участок',
            self::Buildings => 'Здания и сооружения',
            self::Equipment => 'Оборудование и приборы',
            self::FurnitureAndOfficeEquipment => 'Мебель, оргтехника, офисное оборудование',
            self::Vehicles => 'Транспортные средства',
            self::IntangibleAssets => 'Нематериальные активы и ПО',
            self::LeaseholdImprovements => 'Неотделимые улучшения арендованного имущества',
            self::OtherCapitalized => 'Другое',
            self::InitialInventory => 'Первоначальные запасы',
            self::DeferredMarketingAndTraining => 'Расходы на рекламу, маркетинг, обучение и др.',
            self::OtherDeferredExpense => 'Другое',
        };
    }

    public function group(): InvestmentGroup
    {
        return match ($this) {
            self::Land,
            self::Buildings,
            self::Equipment,
            self::FurnitureAndOfficeEquipment,
            self::Vehicles,
            self::IntangibleAssets,
            self::LeaseholdImprovements,
            self::OtherCapitalized => InvestmentGroup::Capitalized,

            self::InitialInventory,
            self::DeferredMarketingAndTraining,
            self::OtherDeferredExpense => InvestmentGroup::NonCapitalized,
        };
    }

    public function treatment(): InvestmentTreatment
    {
        return match ($this) {
            self::Land => InvestmentTreatment::CapexNonDepreciable,

            self::Buildings,
            self::Equipment,
            self::FurnitureAndOfficeEquipment,
            self::Vehicles,
            self::IntangibleAssets,
            self::LeaseholdImprovements,
            self::OtherCapitalized => InvestmentTreatment::CapexDepreciable,

            self::InitialInventory => InvestmentTreatment::InitialWorkingCapital,

            self::DeferredMarketingAndTraining,
            self::OtherDeferredExpense => InvestmentTreatment::DeferredExpense,
        };
    }
}
