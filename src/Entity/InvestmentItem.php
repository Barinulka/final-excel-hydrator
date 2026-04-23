<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Investments\Enum\InvestmentCategory;
use App\Domain\Investments\Enum\PropertyTaxBase;
use App\Domain\Shared\ValueObject\Money;
use App\Repository\InvestmentItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use InvalidArgumentException;

#[ORM\Entity(repositoryClass: InvestmentItemRepository::class)]
#[ORM\Table(name: 'investment_items')]
class InvestmentItem
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(name: 'investment_block_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private InvestmentBlock $investmentBlock;

    #[ORM\Column(length: 64, enumType: InvestmentCategory::class)]
    private InvestmentCategory $category;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(name: 'initial_cost_rub', type: Types::DECIMAL, precision: 20, scale: 2)]
    private string $initialCostRub;

    #[ORM\Column]
    private bool $vatApplied;

    #[ORM\Column(name: 'useful_life_years', type: Types::INTEGER, nullable: true)]
    private ?int $usefulLifeYears = null;

    #[ORM\Column(name: 'deferred_expense_write_off_years', type: Types::INTEGER, nullable: true)]
    private ?int $deferredExpenseWriteOffYears = null;

    #[ORM\Column(name: 'lease_term_months', type: Types::INTEGER, nullable: true)]
    private ?int $leaseTermMonths = null;

    #[ORM\Column(name: 'depreciation_enabled', type: Types::BOOLEAN, nullable: true)]
    private ?bool $depreciationEnabled = null;

    #[ORM\Column(name: 'land_taxable', type: Types::BOOLEAN, nullable: true)]
    private ?bool $landTaxable = null;

    #[ORM\Column(name: 'property_taxable', type: Types::BOOLEAN, nullable: true)]
    private ?bool $propertyTaxable = null;

    #[ORM\Column(name: 'property_tax_base', length: 32, nullable: true, enumType: PropertyTaxBase::class)]
    private ?PropertyTaxBase $propertyTaxBase = null;

    #[ORM\Column(name: 'cadastral_value_rub', type: Types::DECIMAL, precision: 20, scale: 2, nullable: true)]
    private ?string $cadastralValueRub = null;

    #[ORM\Column(name: 'vehicle_taxable', type: Types::BOOLEAN, nullable: true)]
    private ?bool $vehicleTaxable = null;

    #[ORM\Column(name: 'vehicle_power_hp', type: Types::INTEGER, nullable: true)]
    private ?int $vehiclePowerHp = null;

    public static function create(
        InvestmentBlock $investmentBlock,
        InvestmentCategory $category,
        Money $initialCost,
        bool $vatApplied,
        ?string $title = null,
    ): self {
        $item = new self();
        $item->investmentBlock = $investmentBlock;
        $item->category = $category;
        $item->rename($title);
        $item->changeInitialCost($initialCost);
        $item->changeVatApplied($vatApplied);

        $investmentBlock->addItem($item);

        return $item;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvestmentBlock(): InvestmentBlock
    {
        return $this->investmentBlock;
    }

    public function getCategory(): InvestmentCategory
    {
        return $this->category;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function rename(?string $title): static
    {
        $title = $title !== null ? trim($title) : null;
        $this->title = $title === '' ? null : $title;

        return $this;
    }

    public function getInitialCost(): Money
    {
        return Money::fromString($this->initialCostRub);
    }

    public function changeInitialCost(Money $initialCost): static
    {
        $this->initialCostRub = $initialCost->toString();

        return $this;
    }

    public function isVatApplied(): bool
    {
        return $this->vatApplied;
    }

    public function changeVatApplied(bool $vatApplied): static
    {
        $this->vatApplied = $vatApplied;

        return $this;
    }

    public function getUsefulLifeYears(): ?int
    {
        return $this->usefulLifeYears;
    }

    public function changeUsefulLifeYears(?int $usefulLifeYears): static
    {
        $this->assertPositiveOrNull($usefulLifeYears, 'Срок полезного использования должен быть положительным числом.');
        $this->usefulLifeYears = $usefulLifeYears;

        return $this;
    }

    public function getDeferredExpenseWriteOffYears(): ?int
    {
        return $this->deferredExpenseWriteOffYears;
    }

    public function changeDeferredExpenseWriteOffYears(?int $deferredExpenseWriteOffYears): static
    {
        $this->assertPositiveOrNull($deferredExpenseWriteOffYears, 'Срок списания расходов будущих периодов должен быть положительным числом.');
        $this->deferredExpenseWriteOffYears = $deferredExpenseWriteOffYears;

        return $this;
    }

    public function getLeaseTermMonths(): ?int
    {
        return $this->leaseTermMonths;
    }

    public function changeLeaseTermMonths(?int $leaseTermMonths): static
    {
        $this->assertPositiveOrNull($leaseTermMonths, 'Срок договора аренды должен быть положительным числом.');
        $this->leaseTermMonths = $leaseTermMonths;

        return $this;
    }

    public function isDepreciationEnabled(): ?bool
    {
        return $this->depreciationEnabled;
    }

    public function changeDepreciationEnabled(?bool $depreciationEnabled): static
    {
        $this->depreciationEnabled = $depreciationEnabled;

        return $this;
    }

    public function isLandTaxable(): ?bool
    {
        return $this->landTaxable;
    }

    public function changeLandTaxable(?bool $landTaxable): static
    {
        $this->landTaxable = $landTaxable;

        return $this;
    }

    public function isPropertyTaxable(): ?bool
    {
        return $this->propertyTaxable;
    }

    public function changePropertyTaxable(?bool $propertyTaxable): static
    {
        $this->propertyTaxable = $propertyTaxable;

        return $this;
    }

    public function getPropertyTaxBase(): ?PropertyTaxBase
    {
        return $this->propertyTaxBase;
    }

    public function changePropertyTaxBase(?PropertyTaxBase $propertyTaxBase): static
    {
        $this->propertyTaxBase = $propertyTaxBase;

        return $this;
    }

    public function getCadastralValue(): ?Money
    {
        if ($this->cadastralValueRub === null) {
            return null;
        }

        return Money::fromString($this->cadastralValueRub);
    }

    public function changeCadastralValue(?Money $cadastralValue): static
    {
        $this->cadastralValueRub = $cadastralValue?->toString();

        return $this;
    }

    public function isVehicleTaxable(): ?bool
    {
        return $this->vehicleTaxable;
    }

    public function changeVehicleTaxable(?bool $vehicleTaxable): static
    {
        $this->vehicleTaxable = $vehicleTaxable;

        return $this;
    }

    public function getVehiclePowerHp(): ?int
    {
        return $this->vehiclePowerHp;
    }

    public function changeVehiclePowerHp(?int $vehiclePowerHp): static
    {
        $this->assertPositiveOrNull($vehiclePowerHp, 'Мощность транспортного средства должна быть положительным числом.');
        $this->vehiclePowerHp = $vehiclePowerHp;

        return $this;
    }

    private function assertPositiveOrNull(?int $value, string $message): void
    {
        if ($value !== null && $value <= 0) {
            throw new InvalidArgumentException($message);
        }
    }
}
