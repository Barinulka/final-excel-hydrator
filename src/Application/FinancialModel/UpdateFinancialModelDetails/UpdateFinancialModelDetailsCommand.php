<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\UpdateFinancialModelDetails;

use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;

final readonly class UpdateFinancialModelDetailsCommand
{
    public function __construct(
        public User $owner,
        public ShortId $financialModelShortId,
        public string $title,
        public ?string $description,
    ) {
    }
}
