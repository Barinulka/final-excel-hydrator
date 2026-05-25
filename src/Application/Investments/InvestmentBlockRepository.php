<?php

declare(strict_types=1);

namespace App\Application\Investments;

use App\Entity\FinancialModel;
use App\Entity\InvestmentBlock;

interface InvestmentBlockRepository
{
    public function findOneByFinancialModel(FinancialModel $financialModel): ?InvestmentBlock;
    public function save(InvestmentBlock $investmentBlock): void;
}
