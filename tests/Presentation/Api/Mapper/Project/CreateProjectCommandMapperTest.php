<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Api\Mapper\Project;

use App\Entity\User;
use App\Presentation\Api\Mapper\Project\CreateProjectCommandMapper;
use App\Presentation\Api\Request\Project\CreateProjectApiRequest;
use PHPUnit\Framework\TestCase;

final class CreateProjectCommandMapperTest extends TestCase
{
    public function testMapsApiRequestToCreateProjectCommand(): void
    {
        $owner = new User();
        $apiRequest = new CreateProjectApiRequest(
            title: 'Test title',
            description: 'Test description',
        );

        $mapper = new CreateProjectCommandMapper();

        $command = $mapper->map(
            owner: $owner,
            apiRequest: $apiRequest
        );

        self::assertSame($owner, $command->owner);
        self::assertSame('Test title', $command->title);
        self::assertSame('Test description', $command->description);
    }
}
