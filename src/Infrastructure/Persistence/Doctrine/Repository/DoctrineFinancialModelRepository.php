<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\FinancialModel;
use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineFinancialModelRepository implements FinancialModelRepository
{

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(FinancialModel $financialModel): void
    {
        $this->entityManager->persist($financialModel);
    }

    public function findOneByShortIdForOwner(
        ShortId $shortId,
        User $owner
    ): ?FinancialModel {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('f')
            ->from(FinancialModel::class, 'f')
            ->where('f.shortId = :shortId')
            ->join('f.project', 'p')
            ->andWhere('p.owner = :owner')
            ->setParameter('shortId', $shortId->toString())
            ->setParameter('owner', $owner);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function nextVersionNumberForProject(Project $project): int
    {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('MAX(f.versionNumber)')
            ->from(FinancialModel::class, 'f')
            ->where('f.project = :project')
            ->setParameter('project', $project);

        $maxVersionNumber = $qb->getQuery()->getSingleScalarResult();

        return ((int) $maxVersionNumber) + 1;
    }

    public function shortIdExists(ShortId $shortId): bool
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('1')
            ->from(FinancialModel::class, 'f')
            ->where('f.shortId = :shortId')
            ->setParameter('shortId', $shortId->toString())
            ->setMaxResults(1);

        return (bool) $qb->getQuery()->getOneOrNullResult();
    }
}
