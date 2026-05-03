<?php

declare(strict_types=1);

namespace App\Application\Calculation\BuildFinancialModelCalculation;

use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;

final readonly class BuildFinancialModelCalculationQuery
{
    public function __construct(
        public User $owner,
        public ShortId $financialModelShortId,
        public ?ShortId $projectShortId = null,
    ) {
    }
}
