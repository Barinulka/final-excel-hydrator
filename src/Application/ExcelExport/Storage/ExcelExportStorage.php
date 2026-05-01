<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\Storage;

interface ExcelExportStorage
{
    public function resolveExistingFile(string $relativePath): string;
}
