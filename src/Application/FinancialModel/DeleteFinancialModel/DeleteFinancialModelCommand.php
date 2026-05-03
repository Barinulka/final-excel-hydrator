<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\DeleteFinancialModel;

use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;

final readonly class DeleteFinancialModelCommand
{
    public function __construct(
        public User $owner,
        public ShortId $financialModelShortId,
        public ?ShortId $projectShortId = null,
    ) {
    }
}
