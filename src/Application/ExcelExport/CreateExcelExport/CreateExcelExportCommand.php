<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\CreateExcelExport;

use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;

final readonly class CreateExcelExportCommand
{
    public function __construct(
        public User $owner,
        public ShortId $financialModelShortId,
    ) {
    }
}
