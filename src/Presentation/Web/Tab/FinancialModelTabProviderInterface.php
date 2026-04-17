<?php

declare(strict_types=1);

namespace App\Presentation\Web\Tab;

interface FinancialModelTabProviderInterface
{
    public function getDefinition(): FinancialModelTabDefinition;
}
