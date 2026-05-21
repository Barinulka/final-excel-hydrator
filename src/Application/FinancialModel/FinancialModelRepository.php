<?php

declare(strict_types=1);

namespace App\Application\FinancialModel;

use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\FinancialModel;
use App\Entity\User;

interface FinancialModelRepository
{
    public function save(FinancialModel $financialModel): void;
    public function remove(FinancialModel $financialModel): void;
    public function findOneByShortIdForOwner(ShortId $shortId, User $owner): ?FinancialModel;
    public function shortIdExists(ShortId $shortId): bool;
    public function findAllForOwner(User $owner): array;
    public function nextVersionNumberForOwner(User $owner): int;
}
