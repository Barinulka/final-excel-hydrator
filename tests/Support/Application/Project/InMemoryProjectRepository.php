<?php

declare(strict_types=1);

namespace App\Tests\Support\Application\Project;

use App\Application\Project\ProjectRepository;
use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\Project;
use App\Entity\User;

final class InMemoryProjectRepository implements ProjectRepository
{
    /**
     * @var Project[]
     */
    public array $savedProjects = [];

    /**
     * @param string[] $existingShortIds
     */
    public function __construct(
        private array $existingShortIds = [],
    ) {
    }

    public function save(Project $project): void
    {
        $this->savedProjects[] = $project;
    }

    public function findOneByShortIdForOwner(ShortId $shortId, User $owner): ?Project
    {
        foreach ($this->savedProjects as $project) {
            if ($project->getShortId() === $shortId->toString() && $project->getOwner() === $owner) {
                return $project;
            }
        }

        return null;
    }

    public function shortIdExists(ShortId $shortId): bool
    {
        if (in_array($shortId->toString(), $this->existingShortIds, true)) {
            return true;
        }

        foreach ($this->savedProjects as $project) {
            if ($project->getShortId() === $shortId->toString()) {
                return true;
            }
        }

        return false;
    }
}
