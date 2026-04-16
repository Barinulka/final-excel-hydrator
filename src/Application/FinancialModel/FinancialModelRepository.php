<?php

declare(strict_types=1);

namespace App\Application\FinancialModel;

use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\FinancialModel;
use App\Entity\Project;
use App\Entity\User;

interface FinancialModelRepository
{
    public function save(FinancialModel $financialModel): void;
    public function findOneByShortIdForOwner(ShortId $shortId, User $owner): ?FinancialModel;
    public function findOneByShortIdForProjectAndOwner(ShortId $financialModelShortId, ShortId $projectShortId, User $owner): ?FinancialModel;
    public function nextVersionNumberForProject(Project $project): int;
    public function shortIdExists(ShortId $shortId): bool;
    public function findAllForProject(Project $project): array;
}
