<?php

declare(strict_types=1);

namespace App\Tests\Application\Project\UpdateProjectDetails;

use App\Application\Project\UpdateProjectDetails\ArchivedProjectCannotBeUpdatedException;
use App\Application\Project\UpdateProjectDetails\ProjectForUpdateNotFoundException;
use App\Application\Project\UpdateProjectDetails\UpdateProjectDetailsCommand;
use App\Application\Project\UpdateProjectDetails\UpdateProjectDetailsHandler;
use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\Project;
use App\Entity\User;
use App\Tests\Support\Application\Project\InMemoryProjectRepository;
use App\Tests\Support\Application\Shared\Transaction\ImmediateTransactionalRunner;
use PHPUnit\Framework\TestCase;

final class UpdateProjectDetailsHandlerTest extends TestCase
{
    public function testUpdatesProjectDetailsSuccessfully(): void
    {
        $owner = new User();
        $project = $this->createProject($owner);
        $repository = new InMemoryProjectRepository();
        $repository->save($project);
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new UpdateProjectDetailsHandler(
            projectRepository: $repository,
            transactionalRunner: $transactionalRunner,
        );

        $result = $handler->handle($this->createCommand(
            owner: $owner,
            title: '  New Project Title  ',
            description: '  New description  ',
        ));

        self::assertSame('23456789ab', $result->projectShortId);
        self::assertSame('New Project Title', $result->title);
        self::assertSame('New description', $result->description);
        self::assertSame('New Project Title', $project->getTitle());
        self::assertSame('New description', $project->getDescription());
        self::assertSame(1, $transactionalRunner->runCount);
        self::assertCount(1, $repository->savedProjects);
        self::assertSame($project, $repository->savedProjects[0]);
    }

    public function testBlankDescriptionBecomesNull(): void
    {
        $owner = new User();
        $project = $this->createProject($owner);
        $repository = new InMemoryProjectRepository();
        $repository->save($project);

        $handler = new UpdateProjectDetailsHandler(
            projectRepository: $repository,
            transactionalRunner: new ImmediateTransactionalRunner(),
        );

        $result = $handler->handle($this->createCommand(
            owner: $owner,
            title: 'Project without description',
            description: '   ',
        ));

        self::assertSame('Project without description', $result->title);
        self::assertNull($result->description);
        self::assertNull($project->getDescription());
    }

    public function testThrowsWhenProjectDoesNotExist(): void
    {
        $repository = new InMemoryProjectRepository();
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new UpdateProjectDetailsHandler(
            projectRepository: $repository,
            transactionalRunner: $transactionalRunner,
        );

        try {
            $handler->handle($this->createCommand(new User()));

            self::fail('Expected project not found exception.');
        } catch (ProjectForUpdateNotFoundException) {
            self::assertSame(1, $transactionalRunner->runCount);
            self::assertSame([], $repository->savedProjects);
        }
    }

    public function testThrowsWhenProjectBelongsToAnotherOwner(): void
    {
        $projectOwner = new User();
        $commandOwner = new User();
        $project = $this->createProject($projectOwner);
        $repository = new InMemoryProjectRepository();
        $repository->save($project);

        $handler = new UpdateProjectDetailsHandler(
            projectRepository: $repository,
            transactionalRunner: new ImmediateTransactionalRunner(),
        );

        try {
            $handler->handle($this->createCommand($commandOwner));

            self::fail('Expected project not found exception.');
        } catch (ProjectForUpdateNotFoundException) {
            self::assertSame('Test Project', $project->getTitle());
            self::assertSame('Test description', $project->getDescription());
            self::assertCount(1, $repository->savedProjects);
            self::assertSame($project, $repository->savedProjects[0]);
        }
    }

    public function testThrowsWhenProjectIsArchived(): void
    {
        $owner = new User();
        $project = $this->createProject($owner);
        $project->archive();
        $repository = new InMemoryProjectRepository();
        $repository->save($project);
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new UpdateProjectDetailsHandler(
            projectRepository: $repository,
            transactionalRunner: $transactionalRunner,
        );

        try {
            $handler->handle($this->createCommand($owner));

            self::fail('Expected archived project cannot be updated exception.');
        } catch (ArchivedProjectCannotBeUpdatedException) {
            self::assertTrue($project->isArchived());
            self::assertSame('Test Project', $project->getTitle());
            self::assertSame('Test description', $project->getDescription());
            self::assertSame(1, $transactionalRunner->runCount);
            self::assertCount(1, $repository->savedProjects);
            self::assertSame($project, $repository->savedProjects[0]);
        }
    }

    private function createCommand(
        User $owner,
        string $projectShortId = '23456789ab',
        string $title = 'Updated Project',
        ?string $description = 'Updated description',
    ): UpdateProjectDetailsCommand {
        return new UpdateProjectDetailsCommand(
            owner: $owner,
            projectShortId: ShortId::fromString($projectShortId),
            title: $title,
            description: $description,
        );
    }

    private function createProject(User $owner): Project
    {
        return Project::create(
            owner: $owner,
            shortId: ShortId::fromString('23456789ab'),
            title: 'Test Project',
            description: 'Test description',
        );
    }
}
