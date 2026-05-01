# Excel worker

Go-сервис для генерации Excel-файлов.

Worker закрывает lifecycle export-задачи через Symfony internal API и создает `.xlsx` из `CalculationResultPayload`.

## Ответственность

Worker выполняет один проход:

1. Читает конфиг из env.
2. Получает pending export-задачи из Symfony.
3. Берет первую задачу в обработку.
4. Вызывает Excel generator.
5. Помечает задачу как `completed`.
6. При ошибке generator-а помечает задачу как `failed`.

## Env

```text
SYMFONY_INTERNAL_BASE_URL=http://127.0.0.1:7777
STORAGE_ROOT_DIR=var/storage
EXCEL_EXPORTS_DIR=excel-exports
```

`SYMFONY_INTERNAL_BASE_URL` обязателен.

`STORAGE_ROOT_DIR` опционален. Значение по умолчанию:

```text
var/storage
```

`EXCEL_EXPORTS_DIR` опционален. Значение по умолчанию:

```text
excel-exports
```

## Локальный запуск

```bash
go fmt ./...
go test ./...
SYMFONY_INTERNAL_BASE_URL=http://127.0.0.1:7777 go run ./cmd/excel-worker
```

Локально файл будет сохранен в:

```text
var/storage/excel-exports/excel-export-{id}.xlsx
```

В Symfony worker отправит относительный `filePath`:

```text
excel-exports/excel-export-{id}.xlsx
```

## Docker

Из корня проекта:

```bash
make go-worker-build
make go-worker-run
```

В Docker worker обращается к Symfony через compose service name:

```text
http://nginx
```

## Что уже генерируется

Для временных параметров worker создает workbook с листами:

- `Входные данные`;
- `Временные параметры`.

Данные берутся из `CalculationResultPayload.tables`, в первую очередь из таблицы `timeline`.

## Что пока не реализовано

- download endpoint;
- постоянный worker loop;
- retries;
- service token для internal API.
