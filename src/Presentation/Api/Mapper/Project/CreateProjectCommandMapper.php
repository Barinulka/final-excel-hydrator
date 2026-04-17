<?php

declare(strict_types=1);

namespace App\Presentation\Api\Mapper\Project;

use App\Application\Project\CreateProject\CreateProjectCommand;
use App\Entity\User;
use App\Presentation\Api\Request\Project\CreateProjectApiRequest;

final readonly class CreateProjectCommandMapper
{
    public function map(
        User $owner,
        CreateProjectApiRequest $apiRequest,
    ): CreateProjectCommand {
        return new CreateProjectCommand(
            owner: $owner,
            title: $apiRequest->title,
            description: $apiRequest->description,
        );
    }
}
