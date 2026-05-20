<?php

declare(strict_types=1);

namespace App\Presentation\Api\Request\FinancialModel;

use App\Presentation\Api\Request\ApiRequestValueNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

final readonly class CreateFinancialModelApiRequest
{
    #[Assert\NotBlank(message: 'Укажите название модели.')]
    #[Assert\Length(max: 255, maxMessage: 'Название модели не должно быть длиннее 255 символов.')]
    public ?string $title;

    #[Assert\Length(max: 2000, maxMessage: 'Описание модели не должно быть длиннее 2000 символов.')]
    public ?string $description;

    #[Assert\NotBlank(message: 'Не указана дата начала инвестиций.')]
    #[Assert\Regex(
        pattern: '/^\d{4}-(0[1-9]|1[0-2])$/',
        message: 'Формат даты должен быть YYYY-MM.'
    )]
    public ?string $investmentStartMonth;

    #[Assert\NotBlank(message: 'Укажите длительность инвестиций.')]
    #[Assert\Regex(
        pattern: '/^[1-9]\d*$/',
        message: 'Длительность инвестиций должна быть целым числом больше нуля.'
    )]
    public ?string $investmentDurationMonths;

    #[Assert\NotBlank(message: 'Укажите длительность коммерческой работы.')]
    #[Assert\Regex(
        pattern: '/^[1-9]\d*$/',
        message: 'Длительность коммерческой деятельности должна быть целым числом больше нуля.'
    )]
    public ?string $commercialOperationDurationMonths;

    #[Assert\NotBlank(message: 'Выберите шаг прогнозирования.')]
    #[Assert\Choice(
        choices: ['month', 'quarter', 'year'],
        message: 'Недопустимый шаг прогнозирования.'
    )]
    public ?string $forecastStep;

    public function __construct(
        ?string $title,
        ?string $description,
        ?string $investmentStartMonth,
        ?string $investmentDurationMonths,
        ?string $commercialOperationDurationMonths,
        ?string $forecastStep
    ) {
        $this->title = $title;
        $this->description = $description;
        $this->investmentStartMonth = $investmentStartMonth;
        $this->investmentDurationMonths = $investmentDurationMonths;
        $this->commercialOperationDurationMonths = $commercialOperationDurationMonths;
        $this->forecastStep = $forecastStep;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: ApiRequestValueNormalizer::nullableString($data['title'] ?? null),
            description: ApiRequestValueNormalizer::nullableString($data['description'] ?? null),
            investmentStartMonth: ApiRequestValueNormalizer::nullableString($data['investmentStartMonth'] ?? null),
            investmentDurationMonths: ApiRequestValueNormalizer::nullableString($data['investmentDurationMonths'] ?? null),
            commercialOperationDurationMonths: ApiRequestValueNormalizer::nullableString($data['commercialOperationDurationMonths'] ?? null),
            forecastStep: ApiRequestValueNormalizer::nullableString($data['forecastStep'] ?? null),
        );
    }
}
