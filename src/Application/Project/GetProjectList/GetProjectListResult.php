<?php

declare(strict_types=1);

namespace App\Application\Project\GetProjectList;

final readonly class GetProjectListResult
{
    /**
     * @param ProjectListItem[] $projects
     */
    public function __construct(
        public array $projects,
    ) {
    }
}
