<?php

declare(strict_types=1);

namespace App\Tests\Application\Project\GetProjectPage;

use App\Application\Project\GetProjectPage\GetProjectPageHandler;
use App\Application\Project\GetProjectPage\GetProjectPageQuery;
use App\Application\Project\GetProjectPage\ProjectForPageNotFoundException;
use App\Domain\FinancialModel\Enum\FinancialModelStatus;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\FinancialModel;
use App\Entity\Project;
use App\Entity\TimeParams;
use App\Entity\User;
use App\Tests\Support\Application\FinancialModel\InMemoryFinancialModelRepository;
use App\Tests\Support\Application\Project\InMemoryProjectRepository;
use PHPUnit\Framework\TestCase;

final class GetProjectPageHandlerTest extends TestCase
{
    public function testReturnsProjectPageWithSortedFinancialModels(): void
    {
        $owner = new User();
        $project = $this->createProject($owner);
        $anotherProject = $this->createProject($owner, 'cdefghjkmn', 'Another Project');

        $projectRepository = new InMemoryProjectRepository();
        $projectRepository->save($project);
        $projectRepository->save($anotherProject);

        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($this->createFinancialModel($project, 'ghjkmnpqrs', 'Test Project v3', 3, archived: true));
        $financialModelRepository->save($this->createFinancialModel($project, 'defghjkmnp', 'Test Project v1', 1));
        $financialModelRepository->save($this->createFinancialModel($anotherProject, 'mnpqrstuvw', 'Another Project v1', 1));
        $financialModelRepository->save($this->createFinancialModel($project, 'efghjkmnpq', 'Test Project v2', 2));
        $financialModelRepository->save($this->createFinancialModel($project, 'hjkmnpqrst', 'Test Project v4', 4, archived: true));

        $handler = new GetProjectPageHandler(
            projectRepository: $projectRepository,
            financialModelRepository: $financialModelRepository,
        );

        $result = $handler->handle(new GetProjectPageQuery(
            owner: $owner,
            projectShortId: ShortId::fromString('23456789ab'),
        ));

        self::assertSame('23456789ab', $result->projectShortId);
        self::assertSame('Test Project', $result->projectTitle);
        self::assertSame('Test Description', $result->projectDescription);

        self::assertCount(4, $result->financialModels);

        self::assertSame('defghjkmnp', $result->financialModels[0]->shortId);
        self::assertSame('Test Project v1', $result->financialModels[0]->title);
        self::assertSame(1, $result->financialModels[0]->versionNumber);
        self::assertSame(FinancialModelStatus::Active->value, $result->financialModels[0]->status);
        self::assertFalse($result->financialModels[0]->isArchived);
        self::assertSame('2026-01', $result->financialModels[0]->investmentStartMonth);
        self::assertSame(3, $result->financialModels[0]->investmentDurationMonths);
        self::assertSame(12, $result->financialModels[0]->commercialOperationDurationMonths);
        self::assertSame(15, $result->financialModels[0]->totalDurationMonths);
        self::assertSame(ForecastStep::Month->value, $result->financialModels[0]->forecastStep);
        self::assertSame('мес.', $result->financialModels[0]->forecastStepLabel);
        self::assertSame(4, $result->financialModelCount());
        self::assertSame(2, $result->activeFinancialModelCount());
        self::assertSame(2, $result->archivedFinancialModelCount());

        self::assertSame('efghjkmnpq', $result->financialModels[1]->shortId);
        self::assertSame(2, $result->financialModels[1]->versionNumber);
        self::assertSame(FinancialModelStatus::Active->value, $result->financialModels[1]->status);
        self::assertFalse($result->financialModels[1]->isArchived);

        self::assertSame('ghjkmnpqrs', $result->financialModels[2]->shortId);
        self::assertSame(3, $result->financialModels[2]->versionNumber);
        self::assertSame(FinancialModelStatus::Archived->value, $result->financialModels[2]->status);
        self::assertTrue($result->financialModels[2]->isArchived);

        self::assertSame('hjkmnpqrst', $result->financialModels[3]->shortId);
        self::assertSame(4, $result->financialModels[3]->versionNumber);
        self::assertSame(FinancialModelStatus::Archived->value, $result->financialModels[3]->status);
        self::assertTrue($result->financialModels[3]->isArchived);
    }

    public function testReturnsProjectPageWithoutFinancialModels(): void
    {
        $owner = new User();
        $projectRepository = new InMemoryProjectRepository();
        $projectRepository->save($this->createProject($owner));

        $handler = new GetProjectPageHandler(
            projectRepository: $projectRepository,
            financialModelRepository: new InMemoryFinancialModelRepository(),
        );

        $result = $handler->handle(new GetProjectPageQuery(
            owner: $owner,
            projectShortId: ShortId::fromString('23456789ab'),
        ));

        self::assertSame('23456789ab', $result->projectShortId);
        self::assertSame([], $result->financialModels);
    }

    public function testThrowsWhenProjectDoesNotExist(): void
    {
        $handler = new GetProjectPageHandler(
            projectRepository: new InMemoryProjectRepository(),
            financialModelRepository: new InMemoryFinancialModelRepository(),
        );

        $this->expectException(ProjectForPageNotFoundException::class);

        $handler->handle(new GetProjectPageQuery(
            owner: new User(),
            projectShortId: ShortId::fromString('23456789ab'),
        ));
    }

    public function testThrowsWhenProjectBelongsToAnotherOwner(): void
    {
        $projectRepository = new InMemoryProjectRepository();
        $projectRepository->save($this->createProject(new User()));

        $handler = new GetProjectPageHandler(
            projectRepository: $projectRepository,
            financialModelRepository: new InMemoryFinancialModelRepository(),
        );

        $this->expectException(ProjectForPageNotFoundException::class);

        $handler->handle(new GetProjectPageQuery(
            owner: new User(),
            projectShortId: ShortId::fromString('23456789ab'),
        ));
    }

    private function createProject(User $owner, string $shortId = '23456789ab', string $title = 'Test Project'): Project
    {
        return Project::create(
            owner: $owner,
            shortId: ShortId::fromString($shortId),
            title: $title,
            description: 'Test Description',
        );
    }

    private function createFinancialModel(
        Project $project,
        string $shortId,
        string $title,
        int $versionNumber,
        bool $archived = false,
    ): FinancialModel {
        $financialModel = FinancialModel::create(
            project: $project,
            owner: $project->getOwner(),
            shortId: ShortId::fromString($shortId),
            title: $title,
            description: null,
            versionNumber: $versionNumber,
            timeParams: TimeParams::create(
                investmentStartMonth: YearMonth::fromString('2026-01'),
                investmentDuration: MonthDuration::fromInt(3),
                commercialOperationDuration: MonthDuration::fromInt(12),
                forecastStep: ForecastStep::Month,
            ),
        );

        if ($archived) {
            $financialModel->archive();
        }

        return $financialModel;
    }
}
