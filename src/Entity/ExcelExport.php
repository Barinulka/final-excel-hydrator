<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\ExcelExport\Enum\ExcelExportStatus;
use App\Repository\ExcelExportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

#[ORM\Entity(repositoryClass: ExcelExportRepository::class)]
#[ORM\Table(name: 'excel_exports')]
#[ORM\Index(name: 'excel_exports__project_id__idx', columns: ['project_id'])]
#[ORM\Index(name: 'excel_exports__financial_model_id__idx', columns: ['financial_model_id'])]
#[ORM\Index(name: 'excel_exports__status_created_at__idx', columns: ['status', 'created_at'])]
class ExcelExport
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?Project $project = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private ?FinancialModel $financialModel = null;

    #[ORM\Column(length: 32, enumType: ExcelExportStatus::class)]
    private ?ExcelExportStatus $status = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filePath = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $errorMessage = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $startedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $failedAt = null;

    #[ORM\Column(type: Types::JSON)]
    private array $calculationResultPayload = [];

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function getFinancialModel(): ?FinancialModel
    {
        return $this->financialModel;
    }

    public function getStatus(): ?ExcelExportStatus
    {
        return $this->status;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function getErrorMessage(): ?string
    {
        return $this->errorMessage;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function getFailedAt(): ?\DateTimeImmutable
    {
        return $this->failedAt;
    }

    public static function create(
        Project $project,
        FinancialModel $financialModel,
        array $calculationResultPayload,
    ): self {
        if ($financialModel->getProject() !== $project) {
            throw new \InvalidArgumentException('Финансовая модель не принадлежит указанному проекту.');
        }

        if ([] === $calculationResultPayload) {
            throw new \InvalidArgumentException('Нельзя создать Excel export без CalculationResult payload.');
        }

        $excelExport = new self();
        $excelExport->project = $project;
        $excelExport->financialModel = $financialModel;
        $excelExport->status = ExcelExportStatus::Pending;
        $excelExport->calculationResultPayload = $calculationResultPayload;

        return $excelExport;
    }

    public function markProcessing(): static
    {
        if ($this->status !== ExcelExportStatus::Pending) {
            throw new \InvalidArgumentException('В обработку можно взять только pending Excel export.');
        }

        $this->status = ExcelExportStatus::Processing;
        $this->startedAt = new \DateTimeImmutable();

        return $this;
    }

    public function markCompleted(string $filePath): static
    {
        if ($this->status !== ExcelExportStatus::Processing) {
            throw new \InvalidArgumentException('Завершить можно только processing Excel export.');
        }

        $filePath = trim($filePath);

        if ($filePath === '') {
            throw new \InvalidArgumentException('Нельзя отметить Excel export завершенным без filePath.');
        }

        $this->status = ExcelExportStatus::Completed;
        $this->completedAt = new \DateTimeImmutable();
        $this->filePath = $filePath;
        $this->errorMessage = null;

        return $this;
    }

    public function markFailed(string $errorMessage): static
    {
        if ($this->status !== ExcelExportStatus::Processing) {
            throw new \InvalidArgumentException('Ошибкой можно завершить только processing Excel export.');
        }

        $errorMessage = trim($errorMessage);

        if ($errorMessage === '') {
            throw new \InvalidArgumentException('Нельзя отметить Excel export ошибочным без errorMessage.');
        }

        $this->status = ExcelExportStatus::Failed;
        $this->failedAt = new \DateTimeImmutable();
        $this->errorMessage = $errorMessage;

        return $this;
    }

    public function isCompleted(): bool
    {
        return ExcelExportStatus::Completed === $this->status;
    }

    public function getCalculationResultPayload(): array
    {
        return $this->calculationResultPayload;
    }
}
