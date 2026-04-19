<?php

declare(strict_types=1);

namespace App\Presentation\Api\Request\Project;

use App\Presentation\Api\Request\ApiRequestValueNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateProjectDetailsApiRequest
{
    #[Assert\NotBlank(message: 'Укажите название проекта.')]
    #[Assert\Length(max: 255, maxMessage: 'Введите не более 255 символов.')]
    public ?string $title;

    #[Assert\Length(max: 2000, maxMessage: 'Введите не более 2000 символов.')]
    public ?string $description;

    public function __construct(
        ?string $title,
        ?string $description,
    ) {
        $this->title = $title;
        $this->description = $description;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: ApiRequestValueNormalizer::nullableString($data['title'] ?? null),
            description: ApiRequestValueNormalizer::nullableString($data['description'] ?? null),
        );
    }
}
