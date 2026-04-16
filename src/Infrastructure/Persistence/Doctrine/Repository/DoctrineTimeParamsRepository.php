<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine\Repository;

use App\Application\TimeParams\TimeParamsRepository;
use App\Entity\TimeParams;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineTimeParamsRepository implements TimeParamsRepository
{

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function save(TimeParams $timeParams): void
    {
        $this->entityManager->persist($timeParams);
    }
}
