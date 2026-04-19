<?php

declare(strict_types=1);

namespace App\Application\Project\UpdateProjectDetails;

use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;

final readonly class UpdateProjectDetailsCommand
{
    public function __construct(
        public User $owner,
        public ShortId $projectShortId,
        public string $title,
        public ?string $description,
    ) {
    }
}
