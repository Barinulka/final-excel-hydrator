<?php

declare(strict_types=1);

namespace App\Application\Project\CreateProject;

use App\Entity\User;

final readonly class CreateProjectCommand
{
    public function __construct(
        public User $owner,
        public string $title,
        public ?string $description,
    ) {
    }
}
