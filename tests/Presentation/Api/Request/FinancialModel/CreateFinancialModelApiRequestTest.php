<?php

declare(strict_types=1);

namespace App\Tests\Presentation\Api\Request\FinancialModel;

use App\Presentation\Api\Request\FinancialModel\CreateFinancialModelApiRequest;
use PHPUnit\Framework\TestCase;

final class CreateFinancialModelApiRequestTest extends TestCase
{
    public function testCreatesRequestFromValidPayload(): void
    {
        $request = CreateFinancialModelApiRequest::fromArray([
            'title' => ' My financial model ',
            'description' => ' Model description ',
            'investmentStartMonth' => ' 2026-03 ',
            'investmentDurationMonths' => ' 6 ',
            'commercialOperationDurationMonths' => ' 120 ',
            'forecastStep' => ' month ',
        ]);

        self::assertSame('My financial model', $request->title);
        self::assertSame('Model description', $request->description);
        self::assertSame('2026-03', $request->investmentStartMonth);
        self::assertSame('6', $request->investmentDurationMonths);
        self::assertSame('120', $request->commercialOperationDurationMonths);
        self::assertSame('month', $request->forecastStep);
    }

    public function testMissingFieldsBecomeNull(): void
    {
        $request = CreateFinancialModelApiRequest::fromArray([]);

        self::assertNull($request->title);
        self::assertNull($request->description);
        self::assertNull($request->investmentStartMonth);
        self::assertNull($request->investmentDurationMonths);
        self::assertNull($request->commercialOperationDurationMonths);
        self::assertNull($request->forecastStep);
    }

    public function testNonScalarValuesBecomeNull(): void
    {
        $request = CreateFinancialModelApiRequest::fromArray([
            'title' => ['bad'],
            'description' => ['bad'],
            'investmentStartMonth' => ['bad'],
            'investmentDurationMonths' => ['bad'],
            'commercialOperationDurationMonths' => ['bad'],
            'forecastStep' => ['bad'],
        ]);

        self::assertNull($request->title);
        self::assertNull($request->description);
        self::assertNull($request->investmentStartMonth);
        self::assertNull($request->investmentDurationMonths);
        self::assertNull($request->commercialOperationDurationMonths);
        self::assertNull($request->forecastStep);
    }

    public function testBlankStringsAreTrimmedAndLeftForValidator(): void
    {
        $request = CreateFinancialModelApiRequest::fromArray([
            'title' => '   ',
            'description' => '   ',
            'investmentStartMonth' => '   ',
            'investmentDurationMonths' => '   ',
            'commercialOperationDurationMonths' => '   ',
            'forecastStep' => '   ',
        ]);

        self::assertSame('', $request->title);
        self::assertSame('', $request->description);
        self::assertSame('', $request->investmentStartMonth);
        self::assertSame('', $request->investmentDurationMonths);
        self::assertSame('', $request->commercialOperationDurationMonths);
        self::assertSame('', $request->forecastStep);
    }
}
