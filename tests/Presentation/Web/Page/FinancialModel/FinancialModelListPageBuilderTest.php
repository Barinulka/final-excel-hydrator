<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Web\Page\FinancialModel;

use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\ShortId;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Entity\FinancialModel;
use App\Entity\Project;
use App\Entity\TimeParams;
use App\Entity\User;
use App\Presentation\Web\Page\FinancialModel\FinancialModelListItem;
use App\Presentation\Web\Page\FinancialModel\FinancialModelListPage;
use App\Presentation\Web\Page\FinancialModel\FinancialModelListPageBuilder;
use App\Tests\Support\Application\FinancialModel\InMemoryFinancialModelRepository;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FinancialModelListPageBuilderTest extends TestCase
{
    public function testBuildsModelListPageForOwner(): void
    {
        $owner = (new User())->setEmail('owner@example.com');
        $anotherOwner = (new User())->setEmail('other@example.com');

        $project = $this->createProject($owner, '23456789ab');
        $anotherOwnerProject = $this->createProject($anotherOwner, 'cdefghjkmn');

        $repository = new InMemoryFinancialModelRepository();

        $oldActiveModel = $this->createFinancialModel(
            project: $project,
            shortId: 'defghjkmnp',
            title: 'Старая модель',
            versionNumber: 1,
            investmentStartMonth: '2026-01',
            investmentDurationMonths: 3,
            commercialOperationDurationMonths: 12,
            forecastStep: ForecastStep::Month,
        );
        $oldActiveModel->setUpdatedAt(new \DateTime('2026-01-01 10:00:00'));

        $newActiveModel = $this->createFinancialModel(
            project: $project,
            shortId: 'efghjkmnpq',
            title: 'Новая модель',
            versionNumber: 2,
            investmentStartMonth: '2026-04',
            investmentDurationMonths: 6,
            commercialOperationDurationMonths: 24,
            forecastStep: ForecastStep::Quarter,
        );
        $newActiveModel->setUpdatedAt(new \DateTime('2026-02-01 10:00:00'));

        $archivedModel = $this->createFinancialModel(
            project: $project,
            shortId: 'fghjkmnpqr',
            title: 'Архивная модель',
            versionNumber: 3,
            investmentStartMonth: '2026-06',
            investmentDurationMonths: 2,
            commercialOperationDurationMonths: 10,
            forecastStep: ForecastStep::Year,
            archived: true,
        );
        $archivedModel->setUpdatedAt(new \DateTime('2026-03-01 10:00:00'));

        $anotherOwnerModel = $this->createFinancialModel(
            project: $anotherOwnerProject,
            shortId: 'ghjkmnpqrs',
            title: 'Чужая модель',
            versionNumber: 1,
            investmentStartMonth: '2026-08',
            investmentDurationMonths: 4,
            commercialOperationDurationMonths: 18,
            forecastStep: ForecastStep::Month,
        );
        $anotherOwnerModel->setUpdatedAt(new \DateTime('2026-04-01 10:00:00'));

        $repository->save($oldActiveModel);
        $repository->save($archivedModel);
        $repository->save($anotherOwnerModel);
        $repository->save($newActiveModel);

        $page = (new FinancialModelListPageBuilder($repository))->build($owner);

        self::assertSame('owner@example.com', $page->userEmail);
        self::assertSame(3, $page->modelCount());
        self::assertSame('3 модели', $page->modelCountLabel());

        self::assertSame('efghjkmnpq', $page->models[0]->shortId);
        self::assertSame('Новая модель', $page->models[0]->title);
        self::assertSame(2, $page->models[0]->versionNumber);
        self::assertFalse($page->models[0]->isArchived);
        self::assertSame('2026-04', $page->models[0]->investmentStartMonth);
        self::assertSame(30, $page->models[0]->totalDurationMonths);
        self::assertSame('кв.', $page->models[0]->forecastStepLabel);

        self::assertSame('defghjkmnp', $page->models[1]->shortId);
        self::assertSame('Старая модель', $page->models[1]->title);
        self::assertSame(15, $page->models[1]->totalDurationMonths);
        self::assertSame('мес.', $page->models[1]->forecastStepLabel);

        self::assertSame('fghjkmnpqr', $page->models[2]->shortId);
        self::assertSame('Архивная модель', $page->models[2]->title);
        self::assertTrue($page->models[2]->isArchived);
        self::assertSame(12, $page->models[2]->totalDurationMonths);
        self::assertSame('год', $page->models[2]->forecastStepLabel);
    }

    public function testBuildsEmptyModelListPage(): void
    {
        $owner = (new User())->setEmail('owner@example.com');

        $page = (new FinancialModelListPageBuilder(new InMemoryFinancialModelRepository()))->build($owner);

        self::assertSame('owner@example.com', $page->userEmail);
        self::assertSame(0, $page->modelCount());
        self::assertSame('0 моделей', $page->modelCountLabel());
        self::assertSame([], $page->models);
    }

    #[DataProvider('modelCountLabelProvider')]
    public function testBuildsRussianModelCountLabel(int $count, string $expectedLabel): void
    {
        $page = new FinancialModelListPage(
            userEmail: 'owner@example.com',
            models: array_fill(0, $count, new FinancialModelListItem(
                shortId: '23456789ab',
                title: 'Test',
                versionNumber: 1,
                isArchived: false,
                investmentStartMonth: '2026-01',
                totalDurationMonths: 12,
                forecastStepLabel: 'мес.',
            )),
        );

        self::assertSame($expectedLabel, $page->modelCountLabel());
    }

    /**
     * @return iterable<string, array{int, string}>
     */
    public static function modelCountLabelProvider(): iterable
    {
        yield 'one' => [1, '1 модель'];
        yield 'two' => [2, '2 модели'];
        yield 'five' => [5, '5 моделей'];
        yield 'eleven' => [11, '11 моделей'];
        yield 'twenty one' => [21, '21 модель'];
    }

    private function createProject(User $owner, string $shortId): Project
    {
        return Project::create(
            owner: $owner,
            shortId: ShortId::fromString($shortId),
            title: 'Test Project',
            description: null,
        );
    }

    private function createFinancialModel(
        Project $project,
        string $shortId,
        string $title,
        int $versionNumber,
        string $investmentStartMonth,
        int $investmentDurationMonths,
        int $commercialOperationDurationMonths,
        ForecastStep $forecastStep,
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
                investmentStartMonth: YearMonth::fromString($investmentStartMonth),
                investmentDuration: MonthDuration::fromInt($investmentDurationMonths),
                commercialOperationDuration: MonthDuration::fromInt($commercialOperationDurationMonths),
                forecastStep: $forecastStep,
            ),
        );

        if ($archived) {
            $financialModel->archive();
        }

        return $financialModel;
    }
}
