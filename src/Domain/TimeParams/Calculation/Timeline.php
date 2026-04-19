<?php

declare(strict_types=1);

namespace App\Domain\TimeParams\Calculation;

use App\Domain\TimeParams\Enum\ForecastStep;
use DateTimeImmutable;
use InvalidArgumentException;

final readonly class Timeline
{
    /**
     * @param TimelinePeriod[] $periods
     */
    public function __construct(
        private DateTimeImmutable $investmentStartDate,
        private DateTimeImmutable $investmentEndDate,
        private DateTimeImmutable $commercialOperationStartDate,
        private DateTimeImmutable $commercialOperationEndDate,
        private DateTimeImmutable $modelStartDate,
        private DateTimeImmutable $modelEndDate,
        private ForecastStep $forecastStep,
        private array $periods,
    ) {
        if ($this->periods === []) {
            throw new InvalidArgumentException('Временной ряд должен содержать хотя бы один период.');
        }

        if ($this->investmentStartDate > $this->investmentEndDate) {
            throw new InvalidArgumentException('Дата начала инвестиций не может быть позже даты окончания инвестиций.');
        }

        if ($this->commercialOperationStartDate <= $this->investmentEndDate) {
            throw new InvalidArgumentException('Дата начала коммерческой эксплуатации должна быть позже даты окончания инвестиций.');
        }

        if ($this->commercialOperationEndDate < $this->commercialOperationStartDate) {
            throw new InvalidArgumentException('Дата окончания коммерческой эксплуатации не может быть раньше даты ее начала.');
        }

        if ($this->modelStartDate != $this->investmentStartDate) {
            throw new InvalidArgumentException('Дата начала модели должна совпадать с датой начала инвестиций.');
        }

        if ($this->modelEndDate != $this->commercialOperationEndDate) {
            throw new InvalidArgumentException('Дата окончания модели должна совпадать с датой окончания коммерческой эксплуатации.');
        }

        foreach ($this->periods as $period) {
            if (!$period instanceof TimelinePeriod) {
                throw new InvalidArgumentException('Временной ряд может содержать только TimelinePeriod.');
            }
        }
    }

    public function getInvestmentStartDate(): DateTimeImmutable
    {
        return $this->investmentStartDate;
    }

    public function getInvestmentEndDate(): DateTimeImmutable
    {
        return $this->investmentEndDate;
    }

    public function getCommercialOperationStartDate(): DateTimeImmutable
    {
        return $this->commercialOperationStartDate;
    }

    public function getCommercialOperationEndDate(): DateTimeImmutable
    {
        return $this->commercialOperationEndDate;
    }

    public function getModelStartDate(): DateTimeImmutable
    {
        return $this->modelStartDate;
    }

    public function getModelEndDate(): DateTimeImmutable
    {
        return $this->modelEndDate;
    }

    public function getForecastStep(): ForecastStep
    {
        return $this->forecastStep;
    }

    /**
     * @return TimelinePeriod[]
     */
    public function getPeriods(): array
    {
        return $this->periods;
    }

    public function getPeriodCount(): int
    {
        return count($this->periods);
    }
}
