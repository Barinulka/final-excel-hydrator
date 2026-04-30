<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\GetExcelExportsForModel;

use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;

final readonly class GetExcelExportsForModelQuery
{
    public function __construct(
        public User $owner,
        public ShortId $projectShortId,
        public ShortId $financialModelShortId,
    ) {
    }
}
