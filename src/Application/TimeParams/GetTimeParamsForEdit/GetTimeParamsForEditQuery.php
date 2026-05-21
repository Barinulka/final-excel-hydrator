<?php

declare(strict_types=1);

namespace App\Application\TimeParams\GetTimeParamsForEdit;

use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;

final readonly class GetTimeParamsForEditQuery
{
    public function __construct(
        public User $owner,
        public ShortId $financialModelShortId,
    ) {
    }
}
