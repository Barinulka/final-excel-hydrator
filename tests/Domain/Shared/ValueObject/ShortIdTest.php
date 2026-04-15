<?php

declare(strict_types=1);

namespace App\Tests\Domain\Shared\ValueObject;

use App\Domain\Shared\ValueObject\ShortId;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ShortIdTest extends TestCase
{
    public function testItCanBeCreatedFromString(): void
    {
        $shortId = ShortId::fromString('q2wsv56hun');

        self::assertSame('q2wsv56hun', $shortId->toString());
        self::assertSame('q2wsv56hun', (string) $shortId);
    }

    public function testItRejectsTooShortValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ShortId должен содержать 10 символов.');

        ShortId::fromString('q2wsv56hu');
    }

    public function testItRejectsTooLongValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ShortId должен содержать 10 символов.');

        ShortId::fromString('q2wsv56hunx');
    }

    public function testItRejectsInvalidCharacters(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ShortId содержит недопустимые символы.');

        ShortId::fromString('q2wsv56huN');
    }
}
