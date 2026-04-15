<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Shared\ValueObject\MonthDuration;
use App\Domain\Shared\ValueObject\YearMonth;
use App\Domain\TimeParams\Enum\ForecastStep;
use App\Repository\TimeParamsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TimeParamsRepository::class)]
#[ORM\Table(
    name: 'time_params',
    uniqueConstraints: [
        new ORM\UniqueConstraint(name: 'time_params__financial_model_id__uniq', columns: ['financial_model_id']),
    ],
)]
class TimeParams
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'timeParams')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?FinancialModel $financialModel = null;

    #[Assert\NotBlank(message: 'Не указана дата начала инвестиций.')]
    #[ORM\Column(name: 'investment_start_month', type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $investmentStartMonth = null;

    #[Assert\NotBlank(message: 'Не указана длительность инвестиций.')]
    #[Assert\Positive(message: 'Длительность инвестиций должна быть положительным числом.')]
    #[ORM\Column(name: 'investment_duration_months')]
    private ?int $investmentDurationMonths = null;

    #[Assert\NotBlank(message: 'Не указана длительность коммерческой работы.')]
    #[Assert\Positive(message: 'Длительность коммерческой работы должна быть положительным числом.')]
    #[ORM\Column(name: 'commercial_operation_duration_months')]
    private ?int $commercialOperationDurationMonths = null;

    #[Assert\NotBlank(message: 'Не указан шаг прогнозирования.')]
    #[ORM\Column(length: 16, enumType: ForecastStep::class)]
    private ?ForecastStep $forecastStep = null;

    public static function create(
        YearMonth $investmentStartMonth,
        MonthDuration $investmentDuration,
        MonthDuration $commercialOperationDuration,
        ForecastStep $forecastStep,
    ): self {
        $timeParams = new self();
        $timeParams->update(
            $investmentStartMonth,
            $investmentDuration,
            $commercialOperationDuration,
            $forecastStep,
        );

        return $timeParams;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFinancialModel(): ?FinancialModel
    {
        return $this->financialModel;
    }

    public function setFinancialModel(FinancialModel $financialModel): static
    {
        if ($this->financialModel !== null && $this->financialModel !== $financialModel) {
            throw new \InvalidArgumentException('TimeParams уже привязан к другой финансовой модели.');
        }

        $this->financialModel = $financialModel;

        return $this;
    }

    public function getInvestmentStartMonth(): ?YearMonth
    {
        if (!$this->investmentStartMonth instanceof \DateTimeImmutable) {
            return null;
        }

        return YearMonth::fromDate($this->investmentStartMonth);
    }

    public function getInvestmentStartMonthDate(): ?\DateTimeImmutable
    {
        return $this->investmentStartMonth;
    }

    private function setInvestmentStartMonth(YearMonth $investmentStartMonth): static
    {
        $this->investmentStartMonth = $investmentStartMonth->toDate();

        return $this;
    }

    public function getInvestmentDurationMonths(): ?int
    {
        return $this->investmentDurationMonths;
    }

    private function setInvestmentDuration(MonthDuration $investmentDuration): static
    {
        $this->investmentDurationMonths = $investmentDuration->toInt();

        return $this;
    }

    public function getCommercialOperationDurationMonths(): ?int
    {
        return $this->commercialOperationDurationMonths;
    }

    private function setCommercialOperationDuration(MonthDuration $commercialOperationDuration): static
    {
        $this->commercialOperationDurationMonths = $commercialOperationDuration->toInt();

        return $this;
    }

    public function getForecastStep(): ?ForecastStep
    {
        return $this->forecastStep;
    }

    private function setForecastStep(ForecastStep $forecastStep): static
    {
        $this->forecastStep = $forecastStep;

        return $this;
    }

    public function update(
        YearMonth $investmentStartMonth,
        MonthDuration $investmentDuration,
        MonthDuration $commercialOperationDuration,
        ForecastStep $forecastStep,
    ): static {
        $this->setInvestmentStartMonth($investmentStartMonth);
        $this->setInvestmentDuration($investmentDuration);
        $this->setCommercialOperationDuration($commercialOperationDuration);
        $this->setForecastStep($forecastStep);

        return $this;
    }
}
