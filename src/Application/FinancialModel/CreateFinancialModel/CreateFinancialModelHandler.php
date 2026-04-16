<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\CreateFinancialModel;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Application\Project\ProjectRepository;
use App\Application\Shared\ShortId\ShortIdGenerator;
use App\Application\Shared\Transaction\TransactionalRunner;
use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\FinancialModel;
use App\Entity\TimeParams;

final readonly class CreateFinancialModelHandler
{
    private const MAX_SHORT_ID_GENERATION_ATTEMPTS = 5;

    public function __construct(
        private TransactionalRunner $transactionalRunner,
        private ShortIdGenerator $idGenerator,
        private FinancialModelRepository $repository,
        private ProjectRepository $projectRepository,
    ) {
    }

    public function handle(CreateFinancialModelCommand $command): CreateFinancialModelResult
    {
        return $this->transactionalRunner->run(function () use ($command): CreateFinancialModelResult
        {
            $project = $this->projectRepository->findOneByShortIdForOwner($command->projectShortId, $command->owner);

            if (null === $project) {
                throw new ProjectForFinancialModelNotFoundException("Проект '{$command->projectShortId}' не найден");
            }

            $shortId = $this->generateShortId();

            if (null === $shortId) {
                throw new CreateFinancialModelShortIdGenerationException(
                    sprintf(
                        'Не удалось сгенерировать уникальный ShortId после %d попыток, пожалуйста обратитесь к администратору',
                        self::MAX_SHORT_ID_GENERATION_ATTEMPTS
                    )
                );
            }

            $versionNumber = $this->repository->nextVersionNumberForProject($project);

            $timeParams = TimeParams::create(
                investmentStartMonth: $command->investmentStartMonth,
                investmentDuration: $command->investmentDuration,
                commercialOperationDuration: $command->commercialOperationDuration,
                forecastStep: $command->forecastStep,
            );

            $title = $project->getTitle() . ' v' . $versionNumber;

            $financialModel = FinancialModel::create(
                project: $project,
                shortId: $shortId,
                title: $title,
                versionNumber: $versionNumber,
                timeParams: $timeParams,
            );

            $this->repository->save($financialModel);

            return new CreateFinancialModelResult(
                projectShortId: $command->projectShortId->toString(),
                financialModelShortId: $shortId->toString(),
                title: $title,
                versionNumber: $versionNumber,
            );

        });
    }

    private function generateShortId(): ?ShortId
    {
        $shortId = null;

        for ($i = 0; $i < self::MAX_SHORT_ID_GENERATION_ATTEMPTS; $i++) {
            $shortId = $this->idGenerator->generate();

            if (!$this->repository->shortIdExists($shortId)) {
                break;
            }

            $shortId = null;
        }

        return $shortId;
    }
}
