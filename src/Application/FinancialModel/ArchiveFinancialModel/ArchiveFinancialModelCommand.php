<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\ArchiveFinancialModel;

use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;

final readonly class ArchiveFinancialModelCommand
{
    public function __construct(
        public User $owner,
        public ShortId $financialModelShortId,
    ) {
    }
}
