<?php

declare(strict_types=1);

namespace App\Tests\Support\Application\Investments;

use App\Application\Investments\InvestmentBlockRepository;
use App\Entity\FinancialModel;
use App\Entity\InvestmentBlock;

final class InMemoryInvestmentBlockRepository implements InvestmentBlockRepository
{
    /**
     * @var InvestmentBlock[]
     */
    public array $savedInvestmentBlocks = [];

    public function findOneByFinancialModel(FinancialModel $financialModel): ?InvestmentBlock
    {
        foreach ($this->savedInvestmentBlocks as $investmentBlock) {
            if ($investmentBlock->getFinancialModel() === $financialModel) {
                return $investmentBlock;
            }
        }

        return null;
    }

    public function save(InvestmentBlock $investmentBlock): void
    {
        foreach ($this->savedInvestmentBlocks as $savedInvestmentBlock) {
            if ($savedInvestmentBlock === $investmentBlock) {
                return;
            }
        }

        $this->savedInvestmentBlocks[] = $investmentBlock;
    }
}
