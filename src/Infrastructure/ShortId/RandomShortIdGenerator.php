<?php

declare(strict_types=1);

namespace App\Infrastructure\ShortId;

use App\Application\Shared\ShortId\ShortIdGenerator;
use App\Domain\Shared\ValueObject\ShortId;
use Random\RandomException;

class RandomShortIdGenerator implements ShortIdGenerator
{
    private const ALPHABET = '23456789abcdefghjkmnpqrstuvwxyz';
    private const LENGTH = 10;

    /**
     * @throws RandomException
     */
    public function generate(): ShortId
    {
        $maxIndex = strlen(self::ALPHABET) - 1;

        $value = '';
        for ($i = 0; $i < self::LENGTH; $i++) {
            $value .= self::ALPHABET[random_int(0, $maxIndex)];
        }

        return ShortId::fromString($value);
    }
}
