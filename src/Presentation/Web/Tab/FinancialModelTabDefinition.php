<?php

declare(strict_types=1);

namespace App\Presentation\Web\Tab;

final readonly class FinancialModelTabDefinition
{
    public function __construct(
        public string $key,
        public string $label,
        public string $description,
        public string $routeName,
        public string $template,
        public int $order,
        public bool $showInSidebar = true,
    ) {
    }
}
