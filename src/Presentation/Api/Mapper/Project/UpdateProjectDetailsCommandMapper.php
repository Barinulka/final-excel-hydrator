<?php

declare(strict_types=1);

namespace App\Presentation\Api\Mapper\Project;

use App\Application\Project\UpdateProjectDetails\UpdateProjectDetailsCommand;
use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;
use App\Presentation\Api\Request\Project\UpdateProjectDetailsApiRequest;

final readonly class UpdateProjectDetailsCommandMapper
{
    public function map(
        User $owner,
        string $projectShortId,
        UpdateProjectDetailsApiRequest $apiRequest,
    ): UpdateProjectDetailsCommand {
        return new UpdateProjectDetailsCommand(
            owner: $owner,
            projectShortId: ShortId::fromString($projectShortId),
            title: $apiRequest->title,
            description: $apiRequest->description,
        );
    }
}
