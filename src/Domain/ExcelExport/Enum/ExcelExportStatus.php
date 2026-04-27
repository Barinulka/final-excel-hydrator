<?php

declare(strict_types=1);

namespace App\Domain\ExcelExport\Enum;

enum ExcelExportStatus: string
{
    case Pending = 'pending';
    case Processing = 'processing';
    case Completed = 'completed';
    case Failed = 'failed';
}
