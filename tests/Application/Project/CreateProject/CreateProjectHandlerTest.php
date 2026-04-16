<?php

declare(strict_types=1);

namespace App\Tests\Application\Project\CreateProject;

use App\Application\Project\CreateProject\CreateProjectCommand;
use App\Application\Project\CreateProject\CreateProjectHandler;
use App\Application\Project\CreateProject\CreateProjectShortIdGenerationException;
use App\Domain\Project\Enum\ProjectStatus;
use App\Entity\User;
use App\Tests\Support\Application\Project\InMemoryProjectRepository;
use App\Tests\Support\Application\Shared\ShortId\FixedShortIdGenerator;
use App\Tests\Support\Application\Shared\Transaction\ImmediateTransactionalRunner;
use PHPUnit\Framework\TestCase;

final class CreateProjectHandlerTest extends TestCase
{
    public function testCreatesProjectSuccessfully(): void
    {
        $owner = new User();
        $repository = new InMemoryProjectRepository();
        $shortIdGenerator = new FixedShortIdGenerator('23456789ab');
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new CreateProjectHandler(
            $transactionalRunner,
            $shortIdGenerator,
            $repository,
        );

        $result = $handler->handle(new CreateProjectCommand(
            owner: $owner,
            title: '  Test Project  ',
            description: '  Test description  ',
        ));

        self::assertSame('23456789ab', $result->projectShortId);
        self::assertSame('Test Project', $result->title);
        self::assertSame(1, $transactionalRunner->runCount);

        self::assertCount(1, $repository->savedProjects);
        $project = $repository->savedProjects[0];

        self::assertSame($owner, $project->getOwner());
        self::assertSame('23456789ab', $project->getShortId());
        self::assertSame('Test Project', $project->getTitle());
        self::assertSame('Test description', $project->getDescription());
        self::assertSame(ProjectStatus::Active, $project->getStatus());
    }

    public function testRetriesShortIdGenerationWhenCollisionHappens(): void
    {
        $repository = new InMemoryProjectRepository(existingShortIds: ['23456789ab']);
        $shortIdGenerator = new FixedShortIdGenerator('23456789ab', 'cdefghjkmn');

        $handler = new CreateProjectHandler(
            new ImmediateTransactionalRunner(),
            $shortIdGenerator,
            $repository,
        );

        $result = $handler->handle(new CreateProjectCommand(
            owner: new User(),
            title: 'Project',
            description: null,
        ));

        self::assertSame('cdefghjkmn', $result->projectShortId);
        self::assertSame(2, $shortIdGenerator->generatedCount);
        self::assertCount(1, $repository->savedProjects);
        self::assertSame('cdefghjkmn', $repository->savedProjects[0]->getShortId());
    }

    public function testThrowsWhenUniqueShortIdCannotBeGenerated(): void
    {
        $repository = new InMemoryProjectRepository(existingShortIds: ['23456789ab']);
        $shortIdGenerator = new FixedShortIdGenerator(
            '23456789ab',
            '23456789ab',
            '23456789ab',
            '23456789ab',
            '23456789ab',
        );

        $handler = new CreateProjectHandler(
            new ImmediateTransactionalRunner(),
            $shortIdGenerator,
            $repository,
        );

        try {
            $handler->handle(new CreateProjectCommand(
                owner: new User(),
                title: 'Project',
                description: null,
            ));

            self::fail('Expected short id generation exception.');
        } catch (CreateProjectShortIdGenerationException) {
            self::assertSame(5, $shortIdGenerator->generatedCount);
            self::assertSame([], $repository->savedProjects);
        }
    }
}
