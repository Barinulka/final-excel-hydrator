<?php

declare(strict_types=1);

namespace App\Application\Project\GetProjectPage;

use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;

final readonly class GetProjectPageQuery
{
    public function __construct(
        public User $owner,
        public ShortId $projectShortId,
    ) {
    }
}
