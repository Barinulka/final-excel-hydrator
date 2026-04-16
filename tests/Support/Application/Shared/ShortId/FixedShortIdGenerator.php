<?php

declare(strict_types=1);

namespace App\Tests\Support\Application\Shared\ShortId;

use App\Application\Shared\ShortId\ShortIdGenerator;
use App\Domain\Shared\ValueObject\ShortId;
use RuntimeException;

final class FixedShortIdGenerator implements ShortIdGenerator
{
    public int $generatedCount = 0;

    /**
     * @var string[]
     */
    private array $values;

    public function __construct(string ...$values)
    {
        $this->values = $values;
    }

    public function generate(): ShortId
    {
        $value = array_shift($this->values);
        if ($value === null) {
            throw new RuntimeException('No more fixed short ids.');
        }

        ++$this->generatedCount;

        return ShortId::fromString($value);
    }
}
