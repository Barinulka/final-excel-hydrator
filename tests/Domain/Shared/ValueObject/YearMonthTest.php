<?php

declare(strict_types=1);

namespace App\Tests\Domain\Shared\ValueObject;

use App\Domain\Shared\ValueObject\YearMonth;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class YearMonthTest extends TestCase
{
    public function testItCanBeCreatedFromString(): void
    {
        $value = YearMonth::fromString('2026-04');

        self::assertSame('2026-04', $value->toString());
    }

    public function testItRejectsFullDateString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Используйте YYYY-MM формат.');

        YearMonth::fromString('2026-04-04');
    }

    public function testItRejectsInvalidMonth(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Используйте YYYY-MM формат.');

        YearMonth::fromString('2026-13');
    }

    public function testItRejectsZeroMonth(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Используйте YYYY-MM формат.');

        YearMonth::fromString('2026-00');
    }

    public function testItRejectsNonPaddedMonth(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Используйте YYYY-MM формат.');

        YearMonth::fromString('2026-4');
    }

    public function testItRejectsNonDateString(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Используйте YYYY-MM формат.');

        YearMonth::fromString('abc');
    }

    public function testItReturnsFirstDayOfMonth(): void
    {
        $inputDate = new DateTimeImmutable('2026-04-15 14:30:00');

        $yearMonth = YearMonth::fromDate($inputDate);

        $expectedDate = new DateTimeImmutable('2026-04-01 00:00:00');

        self::assertEquals($expectedDate, $yearMonth->toDate());
    }
}
