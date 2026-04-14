<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

use InvalidArgumentException;

final readonly class Money
{
    private function __construct(
        private string $amount,
    ) {
    }

    public static function fromString(string $amount): self
    {
        $normalized = str_replace(',', '.', trim($amount));

        if (!preg_match('/^\d+(\.\d{1,2})?$/', $normalized)) {
            throw new InvalidArgumentException('Значение должно быть положительным дробным числом с 2 цифрами после запятой.');
        }

        if (bccomp($normalized, '0', 2) <= 0) {
            throw new InvalidArgumentException('Money amount must be positive.');
        }

        return new self(self::normalize($normalized));
    }

    public function toString(): string
    {
        return $this->amount;
    }

    private static function normalize(string $amount): string
    {
        if (!str_contains($amount, '.')) {
            return $amount . '.00';
        }

        [$rub, $kop] = explode('.', $amount, 2);

        return $rub . '.' . str_pad($kop, 2, '0');
    }
}
