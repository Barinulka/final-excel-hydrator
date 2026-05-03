<?php

declare(strict_types = 1);

namespace App\Presentation\Api\Mapper\FinancialModel;

use App\Application\FinancialModel\UpdateFinancialModelDetails\UpdateFinancialModelDetailsCommand;
use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;
use App\Presentation\Api\Request\FinancialModel\UpdateFinancialModelDetailsApiRequest;

final readonly class UpdateFinancialModelDetailsCommandMapper
{
    public function map(
        User $owner,
        string $financialModelShortId,
        UpdateFinancialModelDetailsApiRequest $apiRequest,
    ): UpdateFinancialModelDetailsCommand {
        return new UpdateFinancialModelDetailsCommand(
            owner: $owner,
            financialModelShortId: ShortId::fromString($financialModelShortId),
            title: $apiRequest->title,
            description: $apiRequest->description,
        );
    }
}
