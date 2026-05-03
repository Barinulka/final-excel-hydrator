<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\FinancialModel\Enum\AmountDisplayFormat;
use App\Domain\FinancialModel\Enum\FinancialModelStatus;
use App\Domain\Shared\ValueObject\ShortId;
use App\Repository\FinancialModelRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: FinancialModelRepository::class)]
#[ORM\Table(name: 'financial_models')]
#[ORM\UniqueConstraint(name: 'financial_models__short_id__uniq', fields: ['shortId'])]
#[ORM\UniqueConstraint(name: 'financial_models__project_version_number__uniq', columns: ['project_id', 'version_number'])]
#[ORM\Index(name: 'financial_models__project_id__idx', columns: ['project_id'])]
#[ORM\Index(name: 'financial_models__project_id_status__idx', columns: ['project_id', 'status'])]
#[ORM\Index(name: 'financial_models__source_model_id__idx', columns: ['source_model_id'])]
class FinancialModel
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'financialModels')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Project $project = null;

    #[Assert\NotBlank(message: 'Короткий идентификатор обязателен.')]
    #[Assert\Regex(
        pattern: '/^[23456789abcdefghjkmnpqrstuvwxyz]{10}$/',
        message: 'Короткий идентификатор должен содержать 10 разрешенных lowercase-символов.',
    )]
    #[ORM\Column(length: 10)]
    private ?string $shortId = null;

    #[Assert\NotBlank(message: 'Заголовок не может быть пустым.')]
    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[Assert\Positive(message: 'Номер версии должен быть положительным.')]
    #[ORM\Column(name: 'version_number')]
    private ?int $versionNumber = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[ORM\JoinColumn(name: 'source_model_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?self $sourceModel = null;

    #[Assert\NotBlank(message: 'Статус не может быть пустым.')]
    #[ORM\Column(length: 32, enumType: FinancialModelStatus::class)]
    private ?FinancialModelStatus $status = null;

    #[Assert\NotBlank(message: 'Необходимо указать формат вывода значений')]
    #[ORM\Column(length: 16, enumType: AmountDisplayFormat::class)]
    private ?AmountDisplayFormat $amountDisplayFormat = null;

    #[ORM\OneToOne(targetEntity: TimeParams::class, mappedBy: 'financialModel', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?TimeParams $timeParams = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $archivedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    public static function create(
        Project $project,
        ShortId $shortId,
        string $title,
        ?string $description,
        int $versionNumber,
        TimeParams $timeParams,
    ): self {
        $financialModel = new self();
        $financialModel->project = $project;
        $financialModel->shortId = $shortId->toString();
        $financialModel->rename($title);
        $financialModel->setVersionNumber($versionNumber);
        $financialModel->status = FinancialModelStatus::Active;
        $financialModel->amountDisplayFormat = AmountDisplayFormat::WholeRubles;
        $financialModel->setTimeParams($timeParams);
        $financialModel->changeDescription($description);

        return $financialModel;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function getShortId(): ?string
    {
        return $this->shortId;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function rename(string $title): static
    {
        $title = trim($title);
        if ($title === '') {
            throw new \InvalidArgumentException('Название финансовой модели не может быть пустым.');
        }

        $this->title = $title;

        return $this;
    }

    public function getVersionNumber(): ?int
    {
        return $this->versionNumber;
    }

    private function setVersionNumber(int $versionNumber): static
    {
        if ($versionNumber <= 0) {
            throw new \InvalidArgumentException('Номер версии финансовой модели должен быть положительным.');
        }

        $this->versionNumber = $versionNumber;

        return $this;
    }

    public function getSourceModel(): ?self
    {
        return $this->sourceModel;
    }

    public function markAsCopiedFrom(?self $sourceModel): static
    {
        if ($sourceModel === $this) {
            throw new \InvalidArgumentException('Финансовая модель не может быть источником копии для самой себя.');
        }

        $this->sourceModel = $sourceModel;

        return $this;
    }

    public function getStatus(): ?FinancialModelStatus
    {
        return $this->status;
    }

    public function archive(): static
    {
        if ($this->isArchived()) {
            throw new \InvalidArgumentException('Финансовая модель уже находится в архиве.');
        }

        $this->status = FinancialModelStatus::Archived;
        $this->archivedAt = new \DateTimeImmutable();

        return $this;
    }

    public function isArchived(): bool
    {
        return $this->status === FinancialModelStatus::Archived;
    }

    public function isActive(): bool
    {
        return $this->status === FinancialModelStatus::Active;
    }

    public function restore(): static
    {
        if ($this->isActive()) {
            throw new \InvalidArgumentException('Финансовая модель уже активна.');
        }

        $this->status = FinancialModelStatus::Active;
        $this->archivedAt = null;

        return $this;
    }

    public function getAmountDisplayFormat(): ?AmountDisplayFormat
    {
        return $this->amountDisplayFormat;
    }

    public function changeAmountDisplayFormat(AmountDisplayFormat $amountDisplayFormat): static
    {
        $this->amountDisplayFormat = $amountDisplayFormat;

        return $this;
    }

    public function getTimeParams(): ?TimeParams
    {
        return $this->timeParams;
    }

    private function setTimeParams(TimeParams $timeParams): static
    {
        // set the owning side of the relation if necessary
        if ($timeParams->getFinancialModel() !== $this) {
            $timeParams->setFinancialModel($this);
        }

        $this->timeParams = $timeParams;

        return $this;
    }

    public function getArchivedAt(): ?\DateTimeImmutable
    {
        return $this->archivedAt;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function changeDescription(?string $description): static
    {
        $description = trim($description);

        if ($description === '') {
            $description = null;
        }

        $this->description = $description;

        return $this;
    }
}
