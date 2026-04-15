<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

use InvalidArgumentException;

final readonly class ShortId
{
    private const ALPHABET = '23456789abcdefghjkmnpqrstuvwxyz';
    private const LENGTH = 10;

    private function __construct(
        private string $value
    ) {
    }

    public static function fromString(string $value): self
    {
        if (strlen($value) !== self::LENGTH) {
            throw new InvalidArgumentException('ShortId должен содержать 10 символов.');
        }

        if (strspn($value, self::ALPHABET) !== self::LENGTH) {
            throw new InvalidArgumentException('ShortId содержит недопустимые символы.');
        }

        return new self($value);
    }

    public function toString(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
