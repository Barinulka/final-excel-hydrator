<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Investments\Enum\InvestmentExpenseCategoryType;
use App\Repository\InvestmentExpenseCategoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use InvalidArgumentException;

#[ORM\Entity(repositoryClass: InvestmentExpenseCategoryRepository::class)]
#[ORM\Table(name: 'investment_expense_categories')]
class InvestmentExpenseCategory
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'expenseCategories')]
    #[ORM\JoinColumn(name: 'investment_block_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private InvestmentBlock $investmentBlock;

    #[ORM\Column(length: 64, nullable: true, enumType: InvestmentExpenseCategoryType::class)]
    private ?InvestmentExpenseCategoryType $type = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $customTitle = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $relatedExpenses = null;

    private function __construct()
    {
    }

    public static function fromPredefinedType(
        InvestmentBlock $investmentBlock,
        InvestmentExpenseCategoryType $type,
        ?string $relatedExpenses = null,
    ): self {
        $category = new self();
        $category->investmentBlock = $investmentBlock;
        $category->type = $type;
        $category->customTitle = null;
        $category->changeRelatedExpenses($relatedExpenses);

        $investmentBlock->addExpenseCategory($category);

        return $category;
    }

    public static function fromCustomTitle(
        InvestmentBlock $investmentBlock,
        string $customTitle,
        ?string $relatedExpenses = null,
    ): self {
        $customTitle = trim($customTitle);

        if ($customTitle === '') {
            throw new InvalidArgumentException('Название категории инвестиций не должно быть пустым.');
        }

        $category = new self();
        $category->investmentBlock = $investmentBlock;
        $category->type = null;
        $category->customTitle = $customTitle;
        $category->changeRelatedExpenses($relatedExpenses);

        $investmentBlock->addExpenseCategory($category);

        return $category;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvestmentBlock(): InvestmentBlock
    {
        return $this->investmentBlock;
    }

    public function getType(): ?InvestmentExpenseCategoryType
    {
        return $this->type;
    }

    public function getCustomTitle(): ?string
    {
        return $this->customTitle;
    }

    public function getTitle(): string
    {
        if ($this->customTitle !== null) {
            return $this->customTitle;
        }

        if ($this->type === null) {
            throw new InvalidArgumentException('Категория инвестиций должна иметь тип или пользовательское название.');
        }

        return $this->type->label();
    }

    public function getRelatedExpenses(): ?string
    {
        return $this->relatedExpenses;
    }

    public function changeRelatedExpenses(?string $relatedExpenses): void
    {
        $relatedExpenses = $relatedExpenses !== null ? trim($relatedExpenses) : null;

        $this->relatedExpenses = $relatedExpenses === '' ? null : $relatedExpenses;
    }
}
