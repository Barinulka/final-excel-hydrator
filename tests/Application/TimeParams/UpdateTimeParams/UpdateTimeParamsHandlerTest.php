<?php

declare(strict_types=1);

namespace App\Tests\Application\TimeParams\UpdateTimeParams;

use App\Application\TimeParams\UpdateTimeParams\FinancialModelForUpdateTimeParamsNotFoundException;
use App\Application\TimeParams\UpdateTimeParams\UpdateTimeParamsCommand;
use App\Application\TimeParams\UpdateTimeParams\UpdateTimeParamsHandler;
use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\FinancialModel;
use App\Entity\Project;
use App\Entity\TimeParams;
use App\Entity\User;
use App\Tests\Support\Application\FinancialModel\InMemoryFinancialModelRepository;
use App\Tests\Support\Application\Shared\Transaction\ImmediateTransactionalRunner;
use PHPUnit\Framework\TestCase;

final class UpdateTimeParamsHandlerTest extends TestCase
{
    public function testUpdatesTimeParamsSuccessfully(): void
    {
        $owner = new User();
        $financialModel = $this->createFinancialModel($owner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new UpdateTimeParamsHandler(
            transactionalRunner: $transactionalRunner,
            financialModelRepository: $financialModelRepository,
        );

        $result = $handler->handle($this->createCommand($owner));

        self::assertSame('2026-04', $result->investmentStartMonth);
        self::assertSame(6, $result->investmentDurationMonths);
        self::assertSame(24, $result->commercialOperationDurationMonths);
        self::assertSame(ForecastStep::Quarter->value, $result->forecastStep);
        self::assertSame(1, $transactionalRunner->runCount);

        self::assertCount(1, $financialModelRepository->savedFinancialModels);
        self::assertSame($financialModel, $financialModelRepository->savedFinancialModels[0]);

        $timeParams = $financialModel->getTimeParams();

        self::assertNotNull($timeParams);
        self::assertSame('2026-04', $timeParams->getInvestmentStartMonth()?->toString());
        self::assertSame(6, $timeParams->getInvestmentDurationMonths());
        self::assertSame(24, $timeParams->getCommercialOperationDurationMonths());
        self::assertSame(ForecastStep::Quarter, $timeParams->getForecastStep());
    }

    public function testThrowsWhenFinancialModelDoesNotExist(): void
    {
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $transactionalRunner = new ImmediateTransactionalRunner();

        $handler = new UpdateTimeParamsHandler(
            transactionalRunner: $transactionalRunner,
            financialModelRepository: $financialModelRepository,
        );

        try {
            $handler->handle($this->createCommand(new User()));

            self::fail('Expected financial model not found exception.');
        } catch (FinancialModelForUpdateTimeParamsNotFoundException) {
            self::assertSame(1, $transactionalRunner->runCount);
            self::assertSame([], $financialModelRepository->savedFinancialModels);
        }
    }

    public function testThrowsWhenFinancialModelBelongsToAnotherOwner(): void
    {
        $modelOwner = new User();
        $commandOwner = new User();
        $financialModel = $this->createFinancialModel($modelOwner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);

        $handler = new UpdateTimeParamsHandler(
            transactionalRunner: new ImmediateTransactionalRunner(),
            financialModelRepository: $financialModelRepository,
        );

        try {
            $handler->handle($this->createCommand($commandOwner));

            self::fail('Expected financial model not found exception.');
        } catch (FinancialModelForUpdateTimeParamsNotFoundException) {
            self::assertCount(1, $financialModelRepository->savedFinancialModels);

            $timeParams = $financialModel->getTimeParams();

            self::assertNotNull($timeParams);
            self::assertSame('2026-01', $timeParams->getInvestmentStartMonth()?->toString());
            self::assertSame(3, $timeParams->getInvestmentDurationMonths());
            self::assertSame(12, $timeParams->getCommercialOperationDurationMonths());
            self::assertSame(ForecastStep::Month, $timeParams->getForecastStep());
        }
    }

    public function testThrowsWhenProjectShortIdIsWrong(): void
    {
        $owner = new User();
        $financialModel = $this->createFinancialModel($owner);
        $financialModelRepository = new InMemoryFinancialModelRepository();
        $financialModelRepository->save($financialModel);

        $handler = new UpdateTimeParamsHandler(
            transactionalRunner: new ImmediateTransactionalRunner(),
            financialModelRepository: $financialModelRepository,
        );

        try {
            $handler->handle($this->createCommand(
                owner: $owner,
                projectShortId: 'cdefghjkmn',
            ));

            self::fail('Expected financial model not found exception.');
        } catch (FinancialModelForUpdateTimeParamsNotFoundException) {
            self::assertCount(1, $financialModelRepository->savedFinancialModels);

            $timeParams = $financialModel->getTimeParams();

            self::assertNotNull($timeParams);
            self::assertSame('2026-01', $timeParams->getInvestmentStartMonth()?->toString());
            self::assertSame(3, $timeParams->getInvestmentDurationMonths());
            self::assertSame(12, $timeParams->getCommercialOperationDurationMonths());
            self::assertSame(ForecastStep::Month, $timeParams->getForecastStep());
        }
    }

    private function createCommand(User $owner, string $projectShortId = '23456789ab'): UpdateTimeParamsCommand
    {
        return new UpdateTimeParamsCommand(
            financialModelShortId: ShortId::fromString('ab23456789'),
            projectShortId: ShortId::fromString($projectShortId),
            owner: $owner,
            investmentStartMonth: YearMonth::fromString('2026-04'),
            investmentDuration: MonthDuration::fromInt(6),
            commercialOperationDuration: MonthDuration::fromInt(24),
            forecastStep: ForecastStep::Quarter,
        );
    }

    private function createFinancialModel(User $owner): FinancialModel
    {
        $project = Project::create(
            owner: $owner,
            shortId: ShortId::fromString('23456789ab'),
            title: 'Test Project',
            description: null,
        );

        $timeParams = TimeParams::create(
            investmentStartMonth: YearMonth::fromString('2026-01'),
            investmentDuration: MonthDuration::fromInt(3),
            commercialOperationDuration: MonthDuration::fromInt(12),
            forecastStep: ForecastStep::Month,
        );

        return FinancialModel::create(
            project: $project,
            owner: $project->getOwner(),
            shortId: ShortId::fromString('ab23456789'),
            title: 'Test Project v1',
            description: null,
            versionNumber: 1,
            timeParams: $timeParams,
        );
    }
}
