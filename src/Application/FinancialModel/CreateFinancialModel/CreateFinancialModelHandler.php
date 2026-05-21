<?php

declare(strict_types=1);

namespace App\Application\FinancialModel\CreateFinancialModel;

use App\Application\FinancialModel\FinancialModelRepository;
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
    ) {
    }

    public function handle(CreateFinancialModelCommand $command): CreateFinancialModelResult
    {
        return $this->transactionalRunner->run(function () use ($command): CreateFinancialModelResult {
            $shortId = $this->generateShortId();

            if (null === $shortId) {
                throw new CreateFinancialModelShortIdGenerationException(
                    sprintf(
                        'Не удалось сгенерировать уникальный ShortId после %d попыток, пожалуйста обратитесь к администратору',
                        self::MAX_SHORT_ID_GENERATION_ATTEMPTS
                    )
                );
            }

            $versionNumber = $this->repository->nextVersionNumberForOwner($command->owner);

            $timeParams = TimeParams::create(
                investmentStartMonth: $command->investmentStartMonth,
                investmentDuration: $command->investmentDuration,
                commercialOperationDuration: $command->commercialOperationDuration,
                forecastStep: $command->forecastStep,
            );

            $financialModel = FinancialModel::create(
                shortId: $shortId,
                owner: $command->owner,
                title: $command->title,
                description: $command->description,
                versionNumber: $versionNumber,
                timeParams: $timeParams,
            );

            $this->repository->save($financialModel);

            return new CreateFinancialModelResult(
                financialModelShortId: $shortId->toString(),
                title: $command->title,
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
