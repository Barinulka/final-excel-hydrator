<?php

declare(strict_types=1);

namespace App\Application\Project\UpdateProjectDetails;

use App\Application\Project\ProjectRepository;
use App\Application\Shared\Transaction\TransactionalRunner;

final readonly class UpdateProjectDetailsHandler
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private TransactionalRunner $transactionalRunner,
    ) {
    }

    public function handle(UpdateProjectDetailsCommand $command): UpdateProjectDetailsResult
    {
        return $this->transactionalRunner->run(function () use ($command): UpdateProjectDetailsResult {
            $project = $this->projectRepository->findOneByShortIdForOwner(
                shortId: $command->projectShortId,
                owner: $command->owner,
            );

            if (null === $project) {
                throw new ProjectForUpdateNotFoundException("Проект '{$command->projectShortId}' не найден.");
            }

            if ($project->isArchived()) {
                throw new ArchivedProjectCannotBeUpdatedException("Архивный проект '{$command->projectShortId}' нельзя редактировать.");
            }

            $project->rename($command->title);
            $project->changeDescription($command->description);

            $this->projectRepository->save($project);

            return new UpdateProjectDetailsResult(
                projectShortId: $project->getShortId(),
                title: $project->getTitle(),
                description: $project->getDescription(),
            );
        });
    }
}
