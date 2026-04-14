<?php

declare(strict_types=1);

namespace App\Domain\Shared\ValueObject;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class YearMonth
{
    private function __construct(
        private DateTimeImmutable $date,
    ) {
    }

    public static function fromString(string $value): self
    {
        $date = DateTimeImmutable::createFromFormat('!Y-m', $value);
        $errors = DateTimeImmutable::getLastErrors();

        if (
            !$date instanceof DateTimeImmutable
            || ($errors !== false && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $date->format('Y-m') !== $value
        ) {
            throw new InvalidArgumentException('Используйте YYYY-MM формат.');
        }

        return new self($date);
    }

    public static function fromDate(DateTimeImmutable $date): self
    {
        return new self($date->modify('first day of this month')->setTime(0, 0));
    }

    public function toDate(): DateTimeImmutable
    {
        return $this->date;
    }

    public function toString(): string
    {
        return $this->date->format('Y-m');
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
