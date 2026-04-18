<?php

declare(strict_types=1);

namespace App\Presentation\Api\Request\FinancialModel;

use App\Presentation\Api\Request\ApiRequestValueNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class RenameFinancialModelApiRequest
{
    #[Assert\NotBlank(message: 'Укажите название модели.')]
    #[Assert\Length(max: 255, maxMessage: 'Введите не более 255 символов.')]
    public ?string $title;

    public function __construct(
        ?string $title,
    ) {
        $this->title = $title;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: ApiRequestValueNormalizer::nullableString($data['title'] ?? null),
        );
    }
}
