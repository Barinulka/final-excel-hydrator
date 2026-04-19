<?php

declare(strict_types=1);

namespace App\Application\Project\UpdateProjectDetails;

final readonly class UpdateProjectDetailsResult
{
    public function __construct(
        public string $projectShortId,
        public string $title,
        public ?string $description,
    ) {
    }
}
