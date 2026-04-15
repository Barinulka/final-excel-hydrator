<?php

declare(strict_types=1);

namespace App\Tests\Domain\Shared\ValueObject;

use App\Domain\Shared\ValueObject\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function testItCanBeCreatedFromString(): void
    {
        $money = Money::fromString('123');

        self::assertSame('123.00', $money->toString());
    }

    public function testItCanBeCreatedFromDecimalValue(): void
    {
        $money = Money::fromString('123.12');

        self::assertSame('123.12', $money->toString());
    }

    public function testItNormalizesOneDecimalPlace(): void
    {
        $money = Money::fromString('123.4');

        self::assertSame('123.40', $money->toString());
    }

    public function testItAcceptsCommaAsDecimalSeparator(): void
    {
        $money = Money::fromString('123,45');

        self::assertSame('123.45', $money->toString());
    }

    public function testItRejectsNonNumericValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Значение должно быть числом.');

        Money::fromString('qwerty');
    }

    public function testItRejectsInvalidDecimalValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Значение должно быть положительным дробным числом с 2 цифрами после запятой.');

        Money::fromString('123.');
    }

    public function testItRejectsMoreThanTwoDecimalPlaces(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Значение должно быть положительным дробным числом с 2 цифрами после запятой.');

        Money::fromString('123.456');
    }

    public function testItRejectsZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Значение должно быть положительным.');

        Money::fromString('0');
    }

    public function testItRejectsNegativeValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Значение должно быть положительным дробным числом с 2 цифрами после запятой.');

        Money::fromString('-1');
    }
}
