<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\FinancialModel\FinancialModelRepository;
use App\Domain\FinancialModel\Enum\FinancialModelStatus;
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

    public function remove(FinancialModel $financialModel): void
    {
        $this->entityManager->remove($financialModel);
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

    public function findAllForProject(Project $project): array
    {
       return $this->entityManager->createQueryBuilder()
           ->select('f')
           ->from(FinancialModel::class, 'f')
           ->where('f.project = :project')
           ->setParameter('project', $project)
           ->orderBy('CASE WHEN f.status = :active THEN 0 ELSE 1 END', 'ASC')
           ->addOrderBy('f.versionNumber', 'ASC')
           ->setParameter('active', FinancialModelStatus::Active)
           ->getQuery()
           ->getResult();
    }

    public function findOneByShortIdForProjectAndOwner(
        ShortId $financialModelShortId,
        ShortId $projectShortId,
        User $owner
    ): ?FinancialModel {
        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('f')
            ->from(FinancialModel::class, 'f')
            ->where('f.shortId = :modelShortId')
            ->join('f.project', 'p')
            ->andWhere('p.owner = :owner')
            ->andWhere('p.shortId = :projectShortId')
            ->setParameter('modelShortId', $financialModelShortId->toString())
            ->setParameter('owner', $owner)
            ->setParameter('projectShortId', $projectShortId->toString());

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findAllForOwner(User $owner): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('f')
            ->from(FinancialModel::class, 'f')
            ->join('f.project', 'p')
            ->where('p.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('CASE WHEN f.status = :active THEN 0 ELSE 1 END', 'ASC')
            ->addOrderBy('f.updatedAt', 'DESC')
            ->setParameter('active', FinancialModelStatus::Active)
            ->getQuery()
            ->getResult();
    }
}
