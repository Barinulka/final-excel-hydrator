<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\InvestmentBlockRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;

#[ORM\Entity(repositoryClass: InvestmentBlockRepository::class)]
#[ORM\Table(name: 'investment_blocks')]
#[ORM\UniqueConstraint(name: 'investment_blocks__financial_model_id__uniq', columns: ['financial_model_id'])]
class InvestmentBlock
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\OneToOne]
    #[ORM\JoinColumn(name: 'financial_model_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private FinancialModel $financialModel;

    /**
     * @var Collection<int, InvestmentItem>
     */
    #[ORM\OneToMany(targetEntity: InvestmentItem::class, mappedBy: 'investmentBlock')]
    private Collection $items;

    public function __construct(FinancialModel $financialModel)
    {
        $this->financialModel = $financialModel;
        $this->items = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFinancialModel(): FinancialModel
    {
        return $this->financialModel;
    }

    /**
     * @return Collection<int, InvestmentItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(InvestmentItem $item): static
    {
        if ($item->getInvestmentBlock() !== $this) {
            throw new InvalidArgumentException('Строка инвестиций принадлежит другому блоку.');
        }

        if (!$this->items->contains($item)) {
            $this->items->add($item);
        }

        return $this;
    }
}
