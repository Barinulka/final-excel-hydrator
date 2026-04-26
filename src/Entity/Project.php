<?php

declare(strict_types=1);

namespace App\Entity;

use App\Domain\Project\Enum\ProjectStatus;
use App\Domain\Shared\ValueObject\ShortId;
use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
#[ORM\Table(name: 'projects')]
#[ORM\UniqueConstraint(name: 'projects__short_id__uniq', fields: ['shortId'])]
#[ORM\Index(name: 'projects__owner_id__idx', columns: ['owner_id'])]
#[ORM\Index(name: 'projects__owner_id_status__idx', columns: ['owner_id', 'status'])]
class Project
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::BIGINT)]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'projects')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'RESTRICT')]
    private ?User $owner = null;

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

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[Assert\NotBlank(message: 'Статус не может быть пустым.')]
    #[ORM\Column(length: 32, enumType: ProjectStatus::class)]
    private ?ProjectStatus $status = null;

    /**
     * @var Collection<int, FinancialModel>
     */
    #[ORM\OneToMany(targetEntity: FinancialModel::class, mappedBy: 'project')]
    private Collection $financialModels;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $archivedAt = null;

    public function __construct()
    {
        $this->financialModels = new ArrayCollection();
    }

    public static function create(
        User $owner,
        ShortId $shortId,
        string $title,
        ?string $description = null,
    ): self {
        $project = new self();
        $project->owner = $owner;
        $project->shortId = $shortId->toString();
        $project->rename($title);
        $project->changeDescription($description);
        $project->status = ProjectStatus::Active;

        return $project;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
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
            throw new \InvalidArgumentException('Название проекта не может быть пустым.');
        }

        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function changeDescription(?string $description): static
    {
        if ($description === null) {
            $this->description = null;

            return $this;
        }

        $description = trim($description);
        $this->description = $description === '' ? null : $description;

        return $this;
    }

    public function getStatus(): ?ProjectStatus
    {
        return $this->status;
    }

    public function archive(): static
    {
        $this->status = ProjectStatus::Archived;
        $this->archivedAt = new \DateTimeImmutable();

        return $this;
    }

    public function isArchived(): bool
    {
        return $this->status === ProjectStatus::Archived;
    }

    public function isActive(): bool
    {
        return $this->status === ProjectStatus::Active;
    }

    public function restore(): static
    {
        $this->status = ProjectStatus::Active;
        $this->archivedAt = null;

        return $this;
    }

    /**
     * @return Collection<int, FinancialModel>
     */
    public function getFinancialModels(): Collection
    {
        return $this->financialModels;
    }

    public function getArchivedAt(): ?\DateTimeImmutable
    {
        return $this->archivedAt;
    }
}
