<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\Investments\InvestmentBlockRepository;
use App\Entity\FinancialModel;
use App\Entity\InvestmentBlock;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineInvestmentBlockRepository implements InvestmentBlockRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function findOneByFinancialModel(FinancialModel $financialModel): ?InvestmentBlock
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('investmentBlock')
            ->from(InvestmentBlock::class, 'investmentBlock')
            ->andWhere('investmentBlock.financialModel = :financialModel')
            ->setParameter('financialModel', $financialModel)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(InvestmentBlock $investmentBlock): void
    {
        $this->entityManager->persist($investmentBlock);
    }
}
