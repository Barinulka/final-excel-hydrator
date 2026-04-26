<?php

declare(strict_types=1);

namespace App\Application\Project\ArchiveProject;

use App\Application\Project\ProjectRepository;
use App\Application\Shared\Transaction\TransactionalRunner;

final readonly class ArchiveProjectHandler
{
    public function __construct(
        private ProjectRepository $projectRepository,
        private TransactionalRunner $transactionalRunner,
    ) {
    }

    public function handle(ArchiveProjectCommand $command): ArchiveProjectResult
    {
        return $this->transactionalRunner->run(function () use ($command): ArchiveProjectResult {
            $project = $this->projectRepository->findOneByShortIdForOwner(
                shortId: $command->projectShortId,
                owner: $command->owner
            );

            if (null === $project) {
                throw new ProjectForArchiveNotFoundException("Проект '{$command->projectShortId}' не найден.");
            }

            if ($project->isArchived()) {
                throw new ProjectAlreadyArchivedException("Проект '{$command->projectShortId}' уже находится в архиве.");
            }

            $project->archive();
            $this->projectRepository->save($project);

            return new ArchiveProjectResult(
                projectShortId: $command->projectShortId->toString(),
                status: $project->getStatus()->value,
                isArchived: $project->isArchived(),
                archivedAt: $project->getArchivedAt()->format(\DateTimeInterface::ATOM),
            );
        });
    }
}
