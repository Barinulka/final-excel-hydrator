<?php

declare(strict_types=1);

namespace App\Application\Project;

use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\Project;
use App\Entity\User;

interface ProjectRepository
{
    public function save(Project $project): void;
    public function findOneByShortIdForOwner(ShortId $shortId, User $owner): ?Project;
    public function shortIdExists(ShortId $shortId): bool;
}
