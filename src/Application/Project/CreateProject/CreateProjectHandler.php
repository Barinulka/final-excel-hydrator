<?php

declare(strict_types=1);

namespace App\Application\Project\CreateProject;

use App\Application\Project\ProjectRepository;
use App\Application\Shared\ShortId\ShortIdGenerator;
use App\Application\Shared\Transaction\TransactionalRunner;
use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\Project;

final readonly class CreateProjectHandler
{
    private const MAX_SHORT_ID_GENERATION_ATTEMPTS = 5;

    public function __construct(
        private TransactionalRunner $transactionalRunner,
        private ShortIdGenerator $idGenerator,
        private ProjectRepository $repository,
    ) {
    }

    public function handle(CreateProjectCommand $command): CreateProjectResult
    {
        return $this->transactionalRunner->run(function () use ($command): CreateProjectResult
        {
            $shortId = $this->generateShortId();

            if (null === $shortId) {
                throw new CreateProjectShortIdGenerationException(
                    sprintf(
                        'Не удалось сгенерировать уникальный ShortId после %d попыток, пожалуйста обратитесь к администратору',
                        self::MAX_SHORT_ID_GENERATION_ATTEMPTS
                    )
                );
            }

            $project = Project::create(
                owner: $command->owner,
                shortId: $shortId,
                title: $command->title,
                description: $command->description,
            );

            $this->repository->save($project);

            return new CreateProjectResult(
                projectShortId: $shortId->toString(),
                title: $project->getTitle(),
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
