<?php

declare(strict_types=1);

namespace App\Tests\Support\Application\Shared\Transaction;

use App\Application\Shared\Transaction\TransactionalRunner;

final class ImmediateTransactionalRunner implements TransactionalRunner
{
    public int $runCount = 0;

    public function run(callable $operation): mixed
    {
        ++$this->runCount;

        return $operation();
    }
}
