<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\DownloadExcelExport;

use App\Domain\Shared\ValueObject\ShortId;
use App\Entity\User;

final readonly class DownloadExcelExportQuery
{
    public function __construct(
        public User $owner,
        public ShortId $financialModelShortId,
        public int $exportId,
        public ?ShortId $projectShortId = null,
    ) {
    }
}
