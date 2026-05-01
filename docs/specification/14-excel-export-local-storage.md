# 14. Локальный storage для Excel export

## Цель

Сделать так, чтобы Go worker сохранял `.xlsx` в папку, доступную Symfony, а в `ExcelExport.filePath` сохранялся переносимый относительный путь.

Это подготовка к download endpoint и будущему переходу на S3.

## Storage contract

Физическая папка проекта:

```text
var/storage/
```

Папка Excel export-файлов внутри storage:

```text
excel-exports/
```

Физический путь файла:

```text
var/storage/excel-exports/excel-export-{id}.xlsx
```

Значение, которое worker отправляет в Symfony и которое сохраняется в `ExcelExport.filePath`:

```text
excel-exports/excel-export-{id}.xlsx
```

## Почему в БД храним относительный путь

Относительный `filePath` не зависит от:

- пути на Mac;
- пути внутри Docker container;
- пути на production-сервере;
- будущего S3 bucket.

Для S3 это значение почти напрямую станет object key:

```text
excel-exports/excel-export-{id}.xlsx
```

## Go env

Worker использует:

```text
STORAGE_ROOT_DIR=../../var/storage
EXCEL_EXPORTS_DIR=excel-exports
```

`STORAGE_ROOT_DIR` обязателен. При локальном запуске из `go-services/excel-worker` он должен указывать на корневую storage-папку Symfony.

В Docker:

```text
STORAGE_ROOT_DIR=/app/var/storage
EXCEL_EXPORTS_DIR=excel-exports
```

## Docker shared storage

В `compose.yaml` для `excel-worker` добавлен bind mount:

```yaml
volumes:
  - ./var/storage:/app/var/storage
```

`php-fpm` уже видит весь проект через:

```yaml
volumes:
  - ./:/app
```

Значит оба сервиса видят одну и ту же директорию:

```text
/app/var/storage
```

## Что реализовано

- `Config` больше не использует `EXCEL_OUTPUT_DIR`;
- добавлены `STORAGE_ROOT_DIR` и `EXCEL_EXPORTS_DIR`;
- `STORAGE_ROOT_DIR` обязателен, чтобы worker не сохранял файлы случайно в рабочую директорию Go-сервиса;
- `Generator` сохраняет файл в `storageRoot/excelExportsDir`;
- `Generator` возвращает относительный path;
- Go-тест проверяет относительный path и открывает файл по физическому path;
- compose service получил shared storage mount;
- Symfony получил storage resolver и download use case.
- Symfony получил API endpoint скачивания готового файла.
- API списка Excel export-задач возвращает `downloadUrl` только для completed export-задач.
- UI вкладки export показывает ссылку `Скачать` только когда файл готов.

## Symfony storage resolver

Добавлен application-level storage contract:

```text
App\Application\ExcelExport\Storage\ExcelExportStorage
```

Local implementation:

```text
App\Application\ExcelExport\Storage\LocalExcelExportStorage
```

`LocalExcelExportStorage` принимает storage root:

```text
%kernel.project_dir%/var/storage
```

и умеет безопасно превратить относительный `filePath` в абсолютный путь к существующему файлу.

Защита:

- пустой путь запрещен;
- absolute path запрещен;
- Windows-style path с `\` запрещен;
- path traversal наружу из `var/storage` запрещен;
- отсутствующий файл считается ошибкой storage.

## Download use case

Добавлен application use case:

```text
src/Application/ExcelExport/DownloadExcelExport/
```

Handler принимает:

- текущего пользователя;
- `projectShortId`;
- `financialModelShortId`;
- `exportId`.

Handler проверяет:

1. Финансовая модель существует и принадлежит пользователю.
2. `ExcelExport` существует.
3. `ExcelExport` относится к этой модели.
4. Export находится в статусе `completed`.
5. `filePath` заполнен.
6. Файл существует в storage.

Result:

```php
final readonly class DownloadExcelExportResult
{
    public function __construct(
        public string $absolutePath,
        public string $downloadName,
    ) {
    }
}
```

Controller возвращает `BinaryFileResponse`, не занимаясь business checks и path validation.

## Download endpoint

Добавлен endpoint:

```text
GET /api/projects/{projectShortId}/models/{financialModelShortId}/exports/excel/{exportId}/download
```

Route name:

```text
api.excel_export.download
```

Ответы:

- `200` + `.xlsx` файл, если export готов и файл найден;
- `404 {"error":"not_found"}`, если export или модель недоступны пользователю;
- `409 {"error":"file_not_ready"}`, если export еще не completed;
- `404 {"error":"file_not_found"}`, если в БД export completed, но файла уже нет в storage.

Фронту не нужно строить путь к файлу руками. API списка экспортов добавляет поле:

```json
{
  "downloadUrl": "/api/projects/.../models/.../exports/excel/15/download"
}
```

Для `pending`, `processing` и `failed` значение `downloadUrl` равно `null`.

`filePath` остается техническим относительным storage key и не считается публичной ссылкой.

## Проверка

```bash
cd go-services/excel-worker
go fmt ./...
go test ./...
```

Docker:

```bash
docker compose build excel-worker
docker compose run --rm excel-worker
```

Symfony:

```bash
docker compose exec php-fpm php bin/console debug:router api.excel_export.download
docker compose exec php-fpm php bin/phpunit tests/Application/ExcelExport/DownloadExcelExport tests/Application/ExcelExport/Storage
docker compose exec php-fpm php bin/console lint:container
```

Ожидаемый результат:

- файл физически появляется в `var/storage/excel-exports/`;
- в Symfony `ExcelExport.filePath` сохраняется как `excel-exports/excel-export-{id}.xlsx`;
- download use case возвращает absolute path только для completed export-а с существующим файлом.
- route `api.excel_export.download` существует;
- список export-задач возвращает `downloadUrl` для готового файла;
- во вкладке export появляется ссылка `Скачать`.

## Что дальше

Следующий этап:

- ручная проверка полного потока: создать export, дождаться worker, скачать файл из браузера;
- после этого можно фиксировать этап коммитом.
