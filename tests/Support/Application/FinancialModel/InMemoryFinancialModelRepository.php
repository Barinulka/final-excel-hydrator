<?php

declare(strict_types=1);

namespace App\Tests\Support\Application\FinancialModel;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Domain\FinancialModel\Enum\FinancialModelStatus;
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
     * @var FinancialModel[]
     */
    public array $removedFinancialModels = [];

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

    public function remove(FinancialModel $financialModel): void
    {
        $this->removedFinancialModels[] = $financialModel;

        $this->savedFinancialModels = array_values(array_filter(
            $this->savedFinancialModels,
            static fn (FinancialModel $savedFinancialModel): bool => $savedFinancialModel !== $financialModel,
        ));
    }

    public function findOneByShortIdForOwner(ShortId $shortId, User $owner): ?FinancialModel
    {
        foreach ($this->savedFinancialModels as $financialModel) {
            if ($financialModel->getShortId() === $shortId->toString() && $financialModel->isOwnedBy($owner)) {
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

    public function findAllForProject(Project $project): array
    {
        $financialModels = array_filter(
            $this->savedFinancialModels,
            static fn (FinancialModel $financialModel): bool => $financialModel->getProject() === $project,
        );

        usort(
            $financialModels,
            static function (FinancialModel $left, FinancialModel $right): int {
                $leftStatusOrder = $left->getStatus() === FinancialModelStatus::Active ? 0 : 1;
                $rightStatusOrder = $right->getStatus() === FinancialModelStatus::Active ? 0 : 1;

                return [$leftStatusOrder, $left->getVersionNumber()]
                    <=> [$rightStatusOrder, $right->getVersionNumber()];
            }
        );

        return array_values($financialModels);
    }

    public function findOneByShortIdForProjectAndOwner(
        ShortId $financialModelShortId,
        ShortId $projectShortId,
        User $owner
    ): ?FinancialModel {
        foreach ($this->savedFinancialModels as $financialModel) {
            $project = $financialModel->getProject();

            if (
                $financialModel->getShortId() === $financialModelShortId->toString()
                && $project?->getShortId() === $projectShortId->toString()
                && $project->getOwner() === $owner
            ) {
                return $financialModel;
            }
        }

        return null;
    }

    public function findAllForOwner(User $owner): array
    {
        $financialModels = array_filter(
            $this->savedFinancialModels,
            static fn (FinancialModel $financialModel): bool => $financialModel->isOwnedBy($owner),
        );

        usort(
            $financialModels,
            static function (FinancialModel $left, FinancialModel $right): int {
                $leftStatusOrder = $left->getStatus() === FinancialModelStatus::Active ? 0 : 1;
                $rightStatusOrder = $right->getStatus() === FinancialModelStatus::Active ? 0 : 1;

                return [
                    $leftStatusOrder,
                    -self::updatedAtTimestamp($left),
                    $left->getTitle(),
                ] <=> [
                    $rightStatusOrder,
                    -self::updatedAtTimestamp($right),
                    $right->getTitle(),
                ];
            },
        );

        return array_values($financialModels);
    }

    public function nextVersionNumberForOwner(User $owner): int
    {
        $maxVersionNumber = 0;

        foreach ($this->savedFinancialModels as $financialModel) {
            if ($financialModel->isOwnedBy($owner)) {
                $maxVersionNumber = max($maxVersionNumber, (int) $financialModel->getVersionNumber());
            }
        }

        return $maxVersionNumber + 1;
    }

    private static function updatedAtTimestamp(FinancialModel $financialModel): int
    {
        $updatedAt = $financialModel->getUpdatedAt();

        if (!$updatedAt instanceof \DateTimeInterface) {
            return 0;
        }

        return $updatedAt->getTimestamp();
    }
}
