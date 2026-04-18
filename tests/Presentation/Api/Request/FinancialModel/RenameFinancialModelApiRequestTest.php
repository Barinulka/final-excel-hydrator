<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Api\Request\FinancialModel;

use App\Presentation\Api\Request\FinancialModel\RenameFinancialModelApiRequest;
use PHPUnit\Framework\TestCase;

final class RenameFinancialModelApiRequestTest extends TestCase
{
    public function testCreatesRequestFromValidPayload(): void
    {
        $request = RenameFinancialModelApiRequest::fromArray([
            'title' => ' Базовый сценарий ',
        ]);

        self::assertSame('Базовый сценарий', $request->title);
    }

    public function testMissingTitleBecomesNull(): void
    {
        $request = RenameFinancialModelApiRequest::fromArray([]);

        self::assertNull($request->title);
    }

    public function testNonScalarTitleBecomesNull(): void
    {
        $request = RenameFinancialModelApiRequest::fromArray([
            'title' => ['bad'],
        ]);

        self::assertNull($request->title);
    }

    public function testBlankTitleIsTrimmedAndLeftForValidator(): void
    {
        $request = RenameFinancialModelApiRequest::fromArray([
            'title' => '   ',
        ]);

        self::assertSame('', $request->title);
    }
}
