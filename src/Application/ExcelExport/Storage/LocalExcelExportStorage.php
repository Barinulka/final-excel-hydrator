<?php

declare(strict_types=1);

namespace App\Application\ExcelExport\Storage;

final readonly class LocalExcelExportStorage implements ExcelExportStorage
{
    public function __construct(
        private string $storageRoot,
    ) {
    }

    public function resolveExistingFile(string $relativePath): string
    {
        $relativePath = trim($relativePath);

        if ('' === $relativePath) {
            throw new InvalidExcelExportStoragePathException('Путь к Excel export-файлу не может быть пустым.');
        }

        if (str_starts_with($relativePath, '/') || str_contains($relativePath, '\\')) {
            throw new InvalidExcelExportStoragePathException('Путь к Excel export-файлу должен быть относительным.');
        }

        $storageRoot = realpath($this->storageRoot);
        if (false === $storageRoot) {
            throw new ExcelExportStorageFileNotFoundException('Storage directory для Excel export не найден.');
        }

        $absolutePath = $storageRoot . DIRECTORY_SEPARATOR . $relativePath;
        $realFilePath = realpath($absolutePath);

        if (false === $realFilePath || !is_file($realFilePath)) {
            throw new ExcelExportStorageFileNotFoundException('Excel export-файл не найден в storage.');
        }

        $storageRootWithSeparator = rtrim($storageRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!str_starts_with($realFilePath, $storageRootWithSeparator)) {
            throw new InvalidExcelExportStoragePathException('Excel export-файл находится вне storage directory.');
        }

        return $realFilePath;
    }
}
