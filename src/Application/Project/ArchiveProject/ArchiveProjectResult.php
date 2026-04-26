<?php

declare(strict_types=1);

namespace App\Application\Project\ArchiveProject;

final readonly class ArchiveProjectResult
{
    public function __construct(
        public string $projectShortId,
        public string $status,
        public bool $isArchived,
        public ?string $archivedAt,
    ) {
    }
}
