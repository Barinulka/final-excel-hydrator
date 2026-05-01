<?php

declare(strict_types=1);

namespace App\Tests\Application\ExcelExport\Storage;

use App\Application\ExcelExport\Storage\ExcelExportStorageFileNotFoundException;
use App\Application\ExcelExport\Storage\InvalidExcelExportStoragePathException;
use App\Application\ExcelExport\Storage\LocalExcelExportStorage;
use PHPUnit\Framework\TestCase;

final class LocalExcelExportStorageTest extends TestCase
{
    public function testResolvesExistingRelativeFile(): void
    {
        $storageRoot = $this->createStorageRoot();
        $filePath = $storageRoot . '/excel-exports/excel-export-15.xlsx';
        mkdir(dirname($filePath), recursive: true);
        file_put_contents($filePath, 'xlsx');
        $storage = new LocalExcelExportStorage($storageRoot);

        $absolutePath = $storage->resolveExistingFile('excel-exports/excel-export-15.xlsx');

        self::assertSame(realpath($filePath), $absolutePath);
    }

    public function testRejectsAbsolutePath(): void
    {
        $storage = new LocalExcelExportStorage($this->createStorageRoot());

        $this->expectException(InvalidExcelExportStoragePathException::class);

        $storage->resolveExistingFile('/tmp/excel-export-15.xlsx');
    }

    public function testRejectsWindowsStylePath(): void
    {
        $storage = new LocalExcelExportStorage($this->createStorageRoot());

        $this->expectException(InvalidExcelExportStoragePathException::class);

        $storage->resolveExistingFile('excel-exports\\excel-export-15.xlsx');
    }

    public function testRejectsPathTraversalOutsideStorageRoot(): void
    {
        $storageRoot = $this->createStorageRoot();
        $outsideFile = dirname($storageRoot) . '/outside.xlsx';
        file_put_contents($outsideFile, 'xlsx');
        $storage = new LocalExcelExportStorage($storageRoot);

        $this->expectException(InvalidExcelExportStoragePathException::class);

        $storage->resolveExistingFile('../outside.xlsx');
    }

    public function testThrowsWhenFileDoesNotExist(): void
    {
        $storage = new LocalExcelExportStorage($this->createStorageRoot());

        $this->expectException(ExcelExportStorageFileNotFoundException::class);

        $storage->resolveExistingFile('excel-exports/missing.xlsx');
    }

    private function createStorageRoot(): string
    {
        $storageRoot = sys_get_temp_dir() . '/excel-export-storage-test-' . bin2hex(random_bytes(8));
        mkdir($storageRoot, recursive: true);

        return $storageRoot;
    }
}
