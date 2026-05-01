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
STORAGE_ROOT_DIR=var/storage
EXCEL_EXPORTS_DIR=excel-exports
```

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
- `Generator` сохраняет файл в `storageRoot/excelExportsDir`;
- `Generator` возвращает относительный path;
- Go-тест проверяет относительный path и открывает файл по физическому path;
- compose service получил shared storage mount.

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

Ожидаемый результат:

- файл физически появляется в `var/storage/excel-exports/`;
- в Symfony `ExcelExport.filePath` сохраняется как `excel-exports/excel-export-{id}.xlsx`.

## Что дальше

Следующий этап:

- Symfony download use case;
- API endpoint скачивания;
- UI-ссылка для completed export-задач.
