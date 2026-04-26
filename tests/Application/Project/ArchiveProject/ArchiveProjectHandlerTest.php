<?php

declare(strict_types=1);

namespace App\Tests\Application\Project\ArchiveProject;

use App\Application\Project\ArchiveProject\ArchiveProjectCommand;
use App\Application\Project\ArchiveProject\ArchiveProjectHandler;
use App\Application\Project\ArchiveProject\ProjectAlreadyArchivedException;
use App\Application\Project\ArchiveProject\ProjectForArchiveNotFoundException;
use App\Domain\Project\Enum\ProjectStatus;
use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\Project;
use App\Entity\User;
use App\Tests\Support\Application\Project\InMemoryProjectRepository;
use App\Tests\Support\Application\Shared\Transaction\ImmediateTransactionalRunner;
use PHPUnit\Framework\TestCase;

final class ArchiveProjectHandlerTest extends TestCase
{
    public function testItArchivesActiveProjectSuccessfully(): void
    {
        $owner = new User();
        $project = $this->createProject($owner);
        $projectRepository = new InMemoryProjectRepository();
        $projectRepository->save($project);
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new ArchiveProjectHandler(
            projectRepository: $projectRepository,
            transactionalRunner: $transactionalRunner,
        );

        $result = $handler->handle($this->createCommand(
            owner: $owner,
            projectShortId: '23456789ab'
        ));

        self::assertSame('23456789ab', $result->projectShortId);
        self::assertSame(ProjectStatus::Archived->value, $result->status);
        self::assertTrue($result->isArchived);
        self::assertTrue($project->isArchived());
        self::assertFalse($project->isActive());
        self::assertSame(1, $transactionalRunner->runCount);
        self::assertCount(1, $projectRepository->savedProjects);
        self::assertSame($project, $projectRepository->savedProjects[0]);
        self::assertNotNull($project->getArchivedAt());
        self::assertNotNull($result->archivedAt);
        self::assertSame(
            $project->getArchivedAt()->format(\DateTimeInterface::ATOM),
            $result->archivedAt,
        );
    }

    public function testThrowsWhenProjectDoesNotExist(): void
    {
        $projectRepository = new InMemoryProjectRepository();
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new ArchiveProjectHandler(
            projectRepository: $projectRepository,
            transactionalRunner: $transactionalRunner,
        );

        try {
            $handler->handle($this->createCommand(new User()));

            self::fail('Expected project not found exception.');
        } catch (ProjectForArchiveNotFoundException) {
            self::assertSame(1, $transactionalRunner->runCount);
            self::assertSame([], $projectRepository->savedProjects);
        }
    }

    public function testThrowsWhenProjectBelongsToAnotherOwner(): void
    {
        $projectOwner = new User();
        $commandOwner = new User();

        $project = $this->createProject($projectOwner);
        $projectRepository = new InMemoryProjectRepository();
        $projectRepository->save($project);

        $handler = new ArchiveProjectHandler(
            projectRepository: $projectRepository,
            transactionalRunner: new ImmediateTransactionalRunner(),
        );

        try {
            $handler->handle($this->createCommand($commandOwner));

            self::fail('Expected project not found exception.');
        } catch (ProjectForArchiveNotFoundException) {
            self::assertTrue($project->isActive());
            self::assertFalse($project->isArchived());
            self::assertNull($project->getArchivedAt());
            self::assertCount(1, $projectRepository->savedProjects);
            self::assertSame($project, $projectRepository->savedProjects[0]);
        }
    }

    public function testThrowsWhenProjectShortIdDoesNotMatch(): void
    {
        $owner = new User();
        $project = $this->createProject($owner);
        $projectRepository = new InMemoryProjectRepository();
        $projectRepository->save($project);

        $handler = new ArchiveProjectHandler(
            projectRepository: $projectRepository,
            transactionalRunner: new ImmediateTransactionalRunner(),
        );

        try {
            $handler->handle($this->createCommand($owner, projectShortId: '3456789abc'));

            self::fail('Expected project not found exception.');
        } catch (ProjectForArchiveNotFoundException) {
            self::assertTrue($project->isActive());
            self::assertFalse($project->isArchived());
            self::assertNull($project->getArchivedAt());
            self::assertCount(1, $projectRepository->savedProjects);
            self::assertSame($project, $projectRepository->savedProjects[0]);
        }
    }

    public function testThrowsWhenProjectIsAlreadyArchived(): void
    {
        $owner = new User();
        $project = $this->createProject($owner);
        $project->archive();

        $projectRepository = new InMemoryProjectRepository();
        $projectRepository->save($project);
        $transactionalRunner = new ImmediateTransactionalRunner();

        $archivedAt = $project->getArchivedAt();

        $handler = new ArchiveProjectHandler(
            projectRepository: $projectRepository,
            transactionalRunner: $transactionalRunner,
        );

        try {
            $handler->handle($this->createCommand($owner));

            self::fail('Expected project already archived exception.');
        } catch (ProjectAlreadyArchivedException) {
            self::assertTrue($project->isArchived());
            self::assertSame(1, $transactionalRunner->runCount);
            self::assertCount(1, $projectRepository->savedProjects);
            self::assertSame($project, $projectRepository->savedProjects[0]);
            self::assertSame($archivedAt, $project->getArchivedAt());
        }
    }

    private function createProject(User $owner): Project
    {
        return Project::create(
            owner: $owner,
            shortId: ShortId::fromString('23456789ab'),
            title: 'Test Project',
            description: null,
        );
    }

    private function createCommand(
        User $owner,
        string $projectShortId = '23456789ab',
    ): ArchiveProjectCommand {
        return new ArchiveProjectCommand(
            owner: $owner,
            projectShortId: ShortId::fromString($projectShortId),
        );
    }
}
