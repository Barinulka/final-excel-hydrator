<?php

declare(strict_types=1);

namespace App\Application\Project\GetProjectList;

final readonly class ProjectListItem
{
    public function __construct(
        public string $shortId,
        public string $title,
        public ?string $description,
        public string $status,
        public bool $isArchived,
    ) {
    }
}
