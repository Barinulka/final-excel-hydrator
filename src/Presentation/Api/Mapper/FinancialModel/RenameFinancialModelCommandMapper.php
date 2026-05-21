<?php

declare(strict_types=1);

namespace App\Presentation\Api\Mapper\FinancialModel;

use App\Application\FinancialModel\RenameFinancialModel\RenameFinancialModelCommand;
use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;
use App\Presentation\Api\Request\FinancialModel\RenameFinancialModelApiRequest;

final readonly class RenameFinancialModelCommandMapper
{
    public function map(
        User $owner,
        string $financialModelShortId,
        RenameFinancialModelApiRequest $apiRequest,
    ): RenameFinancialModelCommand {
        return new RenameFinancialModelCommand(
            owner: $owner,
            financialModelShortId: ShortId::fromString($financialModelShortId),
            title: $apiRequest->title,
        );
    }
}
