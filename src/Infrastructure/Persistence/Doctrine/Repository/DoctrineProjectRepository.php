<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\Project\ProjectRepository;
use App\Domain\Project\Enum\ProjectStatus;
use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineProjectRepository implements ProjectRepository
{

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(Project $project): void
    {
        $this->entityManager->persist($project);
    }

    public function findOneByShortIdForOwner(
        ShortId $shortId,
        User $owner
    ): ?Project {
        return $this->entityManager
            ->getRepository(Project::class)
            ->findOneBy([
                'shortId' => $shortId->toString(),
                'owner' => $owner,
            ]);
    }

    public function shortIdExists(ShortId $shortId): bool
    {
        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('1')
            ->from(Project::class, 'p')
            ->where('p.shortId = :shortId')
            ->setParameter('shortId', $shortId->toString())
            ->setMaxResults(1);

        return (bool) $qb->getQuery()->getOneOrNullResult();
    }

    public function findAllForOwner(User $owner): array
    {
        return $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(Project::class, 'p')
            ->where('p.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('CASE WHEN p.status = :active THEN 0 ELSE 1 END', 'ASC')
            ->addOrderBy('p.updatedAt', 'DESC')
            ->setParameter('active', ProjectStatus::Active)
            ->getQuery()
            ->getResult();
    }
}
