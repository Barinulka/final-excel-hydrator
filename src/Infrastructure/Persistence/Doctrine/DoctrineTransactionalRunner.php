<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Doctrine;

use App\Application\Shared\Transaction\TransactionalRunner;
use Doctrine\ORM\EntityManagerInterface;

class DoctrineTransactionalRunner implements TransactionalRunner
{
    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * @inheritDoc
     */
    public function run(callable $operation): mixed
    {
        return $this->entityManager->wrapInTransaction($operation);
    }
}
