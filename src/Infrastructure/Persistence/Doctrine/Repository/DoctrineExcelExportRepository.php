<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\ExcelExport\ExcelExportRepository;
use App\Domain\ExcelExport\Enum\ExcelExportStatus;
use App\Entity\ExcelExport;
use App\Entity\FinancialModel;
use Doctrine\ORM\EntityManagerInterface;

final readonly class DoctrineExcelExportRepository implements ExcelExportRepository
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(ExcelExport $excelExport): void
    {
        $this->entityManager->persist($excelExport);
    }

    public function findLatestForFinancialModel(FinancialModel $financialModel): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('excelExport')
            ->from(ExcelExport::class, 'excelExport')
            ->andWhere('excelExport.financialModel = :financialModel')
            ->setParameter('financialModel', $financialModel)
            ->orderBy('excelExport.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findById(int $id): ?ExcelExport
    {
        return $this->entityManager->find(ExcelExport::class, $id);
    }

    public function findPendingForProcessing(int $limit): array
    {
        return $this->entityManager
            ->createQueryBuilder()
            ->select('excelExport')
            ->from(ExcelExport::class, 'excelExport')
            ->andWhere('excelExport.status = :status')
            ->setParameter('status', ExcelExportStatus::Pending)
            ->orderBy('excelExport.createdAt', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
