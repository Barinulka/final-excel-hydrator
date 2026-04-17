<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Api\Request\Project;

use App\Presentation\Api\Request\Project\CreateProjectApiRequest;
use PHPUnit\Framework\TestCase;

final class CreateProjectApiRequestTest extends TestCase
{
    public function testCreatesRequestFromValidPayload(): void
    {
        $request = CreateProjectApiRequest::fromArray([
            'title' => ' Test Project ',
            'description' => ' Description ',
        ]);

        self::assertSame('Test Project', $request->title);
        self::assertSame('Description', $request->description);
    }

    public function testCreatesRequestWithoutDescription(): void
    {
        $request = CreateProjectApiRequest::fromArray([
            'title' => 'Test Project',
        ]);

        self::assertSame('Test Project', $request->title);
        self::assertNull($request->description);
    }

    public function testNonScalarValuesBecomeNull(): void
    {
        $request = CreateProjectApiRequest::fromArray([
            'title' => ['bad'],
            'description' => ['bad'],
        ]);

        self::assertNull($request->title);
        self::assertNull($request->description);
    }

    public function testBlankStringsAreTrimmedAndLeftForValidator(): void
    {
        $request = CreateProjectApiRequest::fromArray([
            'title' => '   ',
            'description' => '   ',
        ]);

        self::assertSame('', $request->title);
        self::assertSame('', $request->description);
    }
}
