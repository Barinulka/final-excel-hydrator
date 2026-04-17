<?php

declare(strict_types=1);

namespace App\Presentation\Api\Request;

final class ApiRequestValueNormalizer
{
    public static function nullableString(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (is_scalar($value)) {
            return trim((string) $value);
        }

        return null;
    }
}
