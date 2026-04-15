<?php

declare(strict_types=1);

namespace App\Tests\Domain\Shared\ValueObject;

use App\Domain\Shared\ValueObject\MonthDuration;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MonthDurationTest extends TestCase
{
    public function testItCanBeCreatedFromPositiveInteger(): void
    {
        $duration = MonthDuration::fromInt(12);

        self::assertSame(12, $duration->toInt());
    }

    public function testItRejectsZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Длительность должна быть целым положительным числом.');

        MonthDuration::fromInt(0);
    }

    public function testItRejectsNegativeInteger(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Длительность должна быть целым положительным числом.');

        MonthDuration::fromInt(-1);
    }
}
