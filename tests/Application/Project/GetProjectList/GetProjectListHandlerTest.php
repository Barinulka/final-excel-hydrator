<?php

declare(strict_types=1);

namespace App\Tests\Application\Project\GetProjectList;

use App\Application\Project\GetProjectList\GetProjectListHandler;
use App\Application\Project\GetProjectList\GetProjectListQuery;
use App\Domain\Project\Enum\ProjectStatus;
use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\Project;
use App\Entity\User;
use App\Tests\Support\Application\Project\InMemoryProjectRepository;
use PHPUnit\Framework\TestCase;

final class GetProjectListHandlerTest extends TestCase
{
    public function testReturnsProjectListForOwner(): void
    {
        $owner = new User();
        $anotherOwner = new User();
        $repository = new InMemoryProjectRepository();

        $oldActiveProject = $this->createProject($owner, '23456789ab', 'Old Active Project');
        $newActiveProject = $this->createProject($owner, 'cdefghjkmn', 'New Active Project');
        $archivedProject = $this->createProject($owner, 'defghjkmnp', 'Archived Project', archived: true);
        $anotherOwnerProject = $this->createProject($anotherOwner, 'efghjkmnpq', 'Another Owner Project');

        $oldActiveProject->setUpdatedAt(new \DateTime('2026-01-01 10:00:00'));
        $newActiveProject->setUpdatedAt(new \DateTime('2026-02-01 10:00:00'));
        $archivedProject->setUpdatedAt(new \DateTime('2026-03-01 10:00:00'));
        $anotherOwnerProject->setUpdatedAt(new \DateTime('2026-04-01 10:00:00'));

        $repository->save($oldActiveProject);
        $repository->save($archivedProject);
        $repository->save($anotherOwnerProject);
        $repository->save($newActiveProject);

        $handler = new GetProjectListHandler($repository);

        $result = $handler->handle(new GetProjectListQuery($owner));

        self::assertCount(3, $result->projects);

        self::assertSame('cdefghjkmn', $result->projects[0]->shortId);
        self::assertSame('New Active Project', $result->projects[0]->title);
        self::assertSame(ProjectStatus::Active->value, $result->projects[0]->status);
        self::assertFalse($result->projects[0]->isArchived);

        self::assertSame('23456789ab', $result->projects[1]->shortId);
        self::assertSame('Old Active Project', $result->projects[1]->title);
        self::assertSame(ProjectStatus::Active->value, $result->projects[1]->status);
        self::assertFalse($result->projects[1]->isArchived);

        self::assertSame('defghjkmnp', $result->projects[2]->shortId);
        self::assertSame('Archived Project', $result->projects[2]->title);
        self::assertSame(ProjectStatus::Archived->value, $result->projects[2]->status);
        self::assertTrue($result->projects[2]->isArchived);
    }

    public function testReturnsEmptyProjectList(): void
    {
        $handler = new GetProjectListHandler(new InMemoryProjectRepository());

        $result = $handler->handle(new GetProjectListQuery(new User()));

        self::assertSame([], $result->projects);
    }

    private function createProject(
        User $owner,
        string $shortId,
        string $title,
        bool $archived = false,
    ): Project {
        $project = Project::create(
            owner: $owner,
            shortId: ShortId::fromString($shortId),
            title: $title,
            description: null,
        );

        if ($archived) {
            $project->archive();
        }

        return $project;
    }
}
