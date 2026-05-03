<?php

declare(strict_types=1);

namespace App\Presentation\Web\Page\FinancialModel;

/**
 * @param FinancialModelListItem[] $models
 */
final readonly class FinancialModelListPage
{
    public function __construct(
        public string $userEmail,
        public array $models,
    ) {
    }

    public function modelCount(): int
    {
        return count($this->models);
    }

    public function modelCountLabel(): string
    {
        $count = $this->modelCount();
        $lastTwoDigits = $count % 100;
        $lastDigit = $count % 10;

        if ($lastTwoDigits >= 11 && $lastTwoDigits <= 14) {
            return sprintf('%d моделей', $count);
        }

        return match ($lastDigit) {
            1 => sprintf('%d модель', $count),
            2, 3, 4 => sprintf('%d модели', $count),
            default => sprintf('%d моделей', $count),
        };
    }
}
