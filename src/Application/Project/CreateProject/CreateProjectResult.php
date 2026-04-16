<?php

declare(strict_types=1);

namespace App\Application\Project\CreateProject;

final readonly class CreateProjectResult
{
    public function __construct(
        public string $projectShortId,
        public string $title,
    ) {
    }
}
