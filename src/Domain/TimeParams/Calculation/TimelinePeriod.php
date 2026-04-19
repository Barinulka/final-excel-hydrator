<?php

declare(strict_types=1);

namespace App\Domain\TimeParams\Calculation;

use App\Domain\Shared\ValueObject\YearMonth;
use DateTimeImmutable;
use InvalidArgumentException;

final readonly class TimelinePeriod
{
    public function __construct(
        private int $periodNumber,
        private DateTimeImmutable $periodStartDate,
        private DateTimeImmutable $periodEndDate,
        private bool $investmentActivity,
        private bool $operatingActivity,
        private bool $operatingStart,
    ) {
        if ($this->periodNumber <= 0) {
            throw new InvalidArgumentException('Номер периода должен быть положительным.');
        }

        if ($this->periodStartDate > $this->periodEndDate) {
            throw new InvalidArgumentException('Дата начала периода не может быть позже даты окончания периода.');
        }

        if ($this->periodStartDate->format('Y-m-d') !== $this->periodStartDate->format('Y-m-01')) {
            throw new InvalidArgumentException('Дата начала периода должна быть первым днем месяца.');
        }

        if ($this->periodStartDate->format('Y-m') !== $this->periodEndDate->format('Y-m')) {
            throw new InvalidArgumentException('Период должен находиться внутри одного месяца.');
        }

        if ($this->periodEndDate->format('Y-m-d') !== $this->periodEndDate->modify('last day of this month')->format('Y-m-d')) {
            throw new InvalidArgumentException('Дата окончания периода должна быть последним днем месяца.');
        }
    }

    public function getPeriodNumber(): int
    {
        return $this->periodNumber;
    }

    public function getYearMonth(): YearMonth
    {
        return YearMonth::fromDate($this->periodStartDate);
    }

    public function getPeriodStartDate(): DateTimeImmutable
    {
        return $this->periodStartDate;
    }

    public function getPeriodEndDate(): DateTimeImmutable
    {
        return $this->periodEndDate;
    }

    public function isInvestmentActivity(): bool
    {
        return $this->investmentActivity;
    }

    public function isOperatingActivity(): bool
    {
        return $this->operatingActivity;
    }

    public function isOperatingStart(): bool
    {
        return $this->operatingStart;
    }
}
