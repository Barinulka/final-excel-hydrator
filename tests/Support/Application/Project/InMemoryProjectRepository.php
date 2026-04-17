<?php

declare(strict_types=1);

namespace App\Tests\Support\Application\Project;

use App\Application\Project\ProjectRepository;
use App\Domain\Project\Enum\ProjectStatus;
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

    public function findAllForOwner(User $owner): array
    {
        $result = array_filter(
            $this->savedProjects,
            static fn (Project $project): bool => $project->getOwner() === $owner,
        );

        usort(
            $result,
            static function (Project $left, Project $right): int {
                $leftStatusOrder = $left->getStatus() === ProjectStatus::Active ? 0 : 1;
                $rightStatusOrder = $right->getStatus() === ProjectStatus::Active ? 0 : 1;

                return [
                    $leftStatusOrder,
                    -self::updatedAtTimestamp($left),
                    $left->getTitle(),
                ] <=> [
                    $rightStatusOrder,
                    -self::updatedAtTimestamp($right),
                    $right->getTitle(),
                ];
            },
        );

        return array_values($result);
    }

    private static function updatedAtTimestamp(Project $project): int
    {
        $updatedAt = $project->getUpdatedAt();

        if (!$updatedAt instanceof \DateTimeInterface) {
            return 0;
        }

        return $updatedAt->getTimestamp();
    }
}
