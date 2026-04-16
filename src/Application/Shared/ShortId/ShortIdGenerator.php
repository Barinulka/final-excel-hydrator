<?php

declare(strict_types=1);

namespace App\Application\Shared\ShortId;

use App\Domain\Shared\ValueObject\ShortId;

interface ShortIdGenerator
{
    public function generate(): ShortId;
}
