<?php

declare(strict_types=1);

namespace App\Application\Project\GetProjectList;

use App\Entity\User;

final readonly class GetProjectListQuery
{
    public function __construct(
        public User $owner,
    ) {
    }
}
