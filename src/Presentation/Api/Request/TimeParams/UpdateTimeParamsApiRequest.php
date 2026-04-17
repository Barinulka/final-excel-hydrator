<?php

declare(strict_types=1);

namespace App\Presentation\Api\Request\TimeParams;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class UpdateTimeParamsApiRequest
{
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
        ?string $investmentStartMonth,
        ?string $investmentDurationMonths,
        ?string $commercialOperationDurationMonths,
        ?string $forecastStep
    ) {
        $this->investmentStartMonth = $investmentStartMonth;
        $this->investmentDurationMonths = $investmentDurationMonths;
        $this->commercialOperationDurationMonths = $commercialOperationDurationMonths;
        $this->forecastStep = $forecastStep;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            investmentStartMonth: self::nullableString($data['investmentStartMonth'] ?? null),
            investmentDurationMonths: self::nullableString($data['investmentDurationMonths'] ?? null),
            commercialOperationDurationMonths:  self::nullableString($data['commercialOperationDurationMonths'] ?? null),
            forecastStep: self::nullableString($data['forecastStep'] ?? null),
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (is_scalar($value)) {
            return trim((string) $value);
        }

        return null;
    }
}
