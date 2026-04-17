<?php

declare(strict_types=1);

namespace App\Tests\Application\TimeParams\GetTimeParamsForEdit;

use App\Application\TimeParams\GetTimeParamsForEdit\GetTimeParamsForEditHandler;
use App\Application\TimeParams\GetTimeParamsForEdit\GetTimeParamsForEditQuery;
use App\Application\TimeParams\GetTimeParamsForEdit\TimeParamsForEditNotFoundException;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\FinancialModel\Enum\FinancialModelStatus;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\FinancialModel;
use App\Entity\Project;
use App\Entity\TimeParams;
use App\Entity\User;
use App\Tests\Support\Application\FinancialModel\InMemoryFinancialModelRepository;
use PHPUnit\Framework\TestCase;

final class GetTimeParamsForEditHandlerTest extends TestCase
{
    public function testReturnsTimeParamsForEditSuccessfully(): void
    {
        $owner = new User();
        $project = $this->createProject($owner);
        $financialModel = $this->createFinancialModel($project);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);

        $handler = new GetTimeParamsForEditHandler($financialModelRepository);

        $result = $handler->handle($this->createQuery($owner));

        self::assertSame('23456789ab', $result->projectShortId);
        self::assertSame('Test Project', $result->projectTitle);
        self::assertSame('ab23456789', $result->financialModelShortId);
        self::assertSame('Test Project v1', $result->financialModelTitle);
        self::assertSame(FinancialModelStatus::Active->value, $result->financialModelStatus);
        self::assertFalse($result->isFinancialModelArchived);
        self::assertSame('2026-04', $result->investmentStartMonth);
        self::assertSame(6, $result->investmentDurationMonths);
        self::assertSame(24, $result->commercialOperationDurationMonths);
        self::assertSame(30, $result->totalDurationMonths);
        self::assertSame(ForecastStep::Quarter->value, $result->forecastStep);
        self::assertSame('кв.', $result->forecastStepLabel);
    }

    public function testThrowsWhenFinancialModelShortIdIsWrong(): void
    {
        $owner = new User();
        $project = $this->createProject($owner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($this->createFinancialModel($project));

        $handler = new GetTimeParamsForEditHandler($financialModelRepository);

        $this->expectException(TimeParamsForEditNotFoundException::class);

        $handler->handle($this->createQuery(
            owner: $owner,
            financialModelShortId: 'cdefghjkmn',
        ));
    }

    public function testThrowsWhenProjectShortIdIsWrong(): void
    {
        $owner = new User();
        $project = $this->createProject($owner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($this->createFinancialModel($project));

        $handler = new GetTimeParamsForEditHandler($financialModelRepository);

        $this->expectException(TimeParamsForEditNotFoundException::class);

        $handler->handle($this->createQuery(
            owner: $owner,
            projectShortId: 'cdefghjkmn',
        ));
    }

    public function testThrowsWhenFinancialModelBelongsToAnotherOwner(): void
    {
        $modelOwner = new User();
        $commandOwner = new User();
        $project = $this->createProject($modelOwner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($this->createFinancialModel($project));

        $handler = new GetTimeParamsForEditHandler($financialModelRepository);

        $this->expectException(TimeParamsForEditNotFoundException::class);

        $handler->handle($this->createQuery($commandOwner));
    }

    private function createQuery(
        User $owner,
        string $projectShortId = '23456789ab',
        string $financialModelShortId = 'ab23456789',
    ): GetTimeParamsForEditQuery {
        return new GetTimeParamsForEditQuery(
            owner: $owner,
            projectShortId: ShortId::fromString($projectShortId),
            financialModelShortId: ShortId::fromString($financialModelShortId),
        );
    }

    private function createProject(User $owner): Project
    {
        return Project::create(
            owner: $owner,
            shortId: ShortId::fromString('23456789ab'),
            title: 'Test Project',
            description: 'Test Description',
        );
    }

    private function createFinancialModel(Project $project): FinancialModel
    {
        return FinancialModel::create(
            project: $project,
            shortId: ShortId::fromString('ab23456789'),
            title: 'Test Project v1',
            versionNumber: 1,
            timeParams: TimeParams::create(
                investmentStartMonth: YearMonth::fromString('2026-04'),
                investmentDuration: MonthDuration::fromInt(6),
                commercialOperationDuration: MonthDuration::fromInt(24),
                forecastStep: ForecastStep::Quarter,
            ),
        );
    }
}
