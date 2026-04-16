<?php

declare(strict_types=1);

namespace App\Tests\Support\Application\FinancialModel;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\FinancialModel;
use App\Entity\Project;
use App\Entity\User;

final class InMemoryFinancialModelRepository implements FinancialModelRepository
{
    /**
     * @var FinancialModel[]
     */
    public array $savedFinancialModels = [];

    /**
     * @param string[] $existingShortIds
     */
    public function __construct(
        private array $existingShortIds = [],
    ) {
    }

    public function save(FinancialModel $financialModel): void
    {
        foreach ($this->savedFinancialModels as $savedFinancialModel) {
            if ($savedFinancialModel === $financialModel) {
                return;
            }
        }

        $this->savedFinancialModels[] = $financialModel;
    }

    public function findOneByShortIdForOwner(ShortId $shortId, User $owner): ?FinancialModel
    {
        foreach ($this->savedFinancialModels as $financialModel) {
            $project = $financialModel->getProject();

            if ($financialModel->getShortId() === $shortId->toString() && $project?->getOwner() === $owner) {
                return $financialModel;
            }
        }

        return null;
    }

    public function nextVersionNumberForProject(Project $project): int
    {
        $maxVersionNumber = 0;

        foreach ($this->savedFinancialModels as $financialModel) {
            if ($financialModel->getProject() === $project) {
                $maxVersionNumber = max($maxVersionNumber, (int) $financialModel->getVersionNumber());
            }
        }

        return $maxVersionNumber + 1;
    }

    public function shortIdExists(ShortId $shortId): bool
    {
        if (in_array($shortId->toString(), $this->existingShortIds, true)) {
            return true;
        }

        foreach ($this->savedFinancialModels as $financialModel) {
            if ($financialModel->getShortId() === $shortId->toString()) {
                return true;
            }
        }

        return false;
    }
}
