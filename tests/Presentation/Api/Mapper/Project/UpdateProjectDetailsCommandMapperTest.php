<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Api\Mapper\Project;

use App\Entity\User;
use App\Presentation\Api\Mapper\Project\UpdateProjectDetailsCommandMapper;
use App\Presentation\Api\Request\Project\UpdateProjectDetailsApiRequest;
use PHPUnit\Framework\TestCase;

final class UpdateProjectDetailsCommandMapperTest extends TestCase
{
    public function testMapsApiRequestToUpdateProjectDetailsCommand(): void
    {
        $owner = new User();
        $apiRequest = new UpdateProjectDetailsApiRequest(
            title: 'Test title',
            description: 'Test description',
        );

        $mapper = new UpdateProjectDetailsCommandMapper();

        $command = $mapper->map(
            owner: $owner,
            projectShortId: '23456789ab',
            apiRequest: $apiRequest,
        );

        self::assertSame($owner, $command->owner);
        self::assertSame('23456789ab', $command->projectShortId->toString());
        self::assertSame('Test title', $command->title);
        self::assertSame('Test description', $command->description);
    }
}
