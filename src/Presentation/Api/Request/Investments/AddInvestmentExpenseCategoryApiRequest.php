<?php

declare(strict_types=1);

namespace App\Presentation\Api\Request\Investments;

use Symfony\Component\Validator\Constraints as Assert;

final class AddInvestmentExpenseCategoryApiRequest
{
    #[Assert\Choice(
        choices: [
            'equipment',
            'furnitureAndOfficeEquipment',
            'renovation',
            'softwareWebsiteLicenses',
            'transport',
            'intangibleAssetsAndPatents',
            'other',
        ],
        message: 'Недопустимый тип категории инвестиций.',
    )]
    public ?string $type = null;

    #[Assert\Length(
        max: 255,
        maxMessage: 'Название категории не должно быть длиннее {{ limit }} символов.',
    )]
    public ?string $customTitle = null;

    public ?string $relatedExpenses = null;
}
