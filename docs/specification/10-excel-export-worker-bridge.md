# 10. Worker bridge и internal API для Excel export

## Цель

Подготовить Symfony-сторону для будущего Go worker-а, который будет генерировать Excel-файлы.

На этом этапе Go worker еще не реализуется. Symfony предоставляет application use cases и internal HTTP API, через которые worker сможет:

1. получить pending export-задачи;
2. взять задачу в обработку;
3. завершить задачу успешно;
4. завершить задачу ошибкой.

## Главное правило

Worker не меняет БД напрямую и не пересчитывает финансовую модель.

Worker работает через internal API Symfony:

```text
Go worker -> Symfony internal API -> Application handler -> ExcelExport entity -> Database
```

## Lifecycle

Поддерживаемые переходы:

```text
pending -> processing -> completed
pending -> processing -> failed
```

Неправильные переходы запрещены в entity `ExcelExport`:

- нельзя взять в обработку задачу, которая уже не `pending`;
- нельзя завершить `completed` задачу, которая не `processing`;
- нельзя завершить `failed` задачу, которая не `processing`.

Application handler-ы не содержат правила переходов статуса. Они находят entity, вызывают доменный метод и сохраняют результат.

## Repository contract

В `ExcelExportRepository` добавлены методы:

```php
public function findById(int $id): ?ExcelExport;

/**
 * @return list<ExcelExport>
 */
public function findPendingForProcessing(int $limit): array;
```

`findPendingForProcessing()` возвращает задачи:

- только со статусом `pending`;
- в порядке `createdAt ASC`;
- не больше указанного `limit`.

Почему `ASC`: worker должен брать самые старые pending-задачи первыми.

Это отличается от UI-списка задач, где используется `createdAt DESC`, потому что пользователю важнее видеть последние задачи сверху.

## Application use cases

### GetPendingExcelExportsForProcessing

Назначение:

Получить список pending-задач для worker-а.

Result item содержит:

- `id`;
- `status`;
- `calculationResultPayload`;
- `createdAt`.

Если задача для worker-а не имеет `id`, handler выбрасывает `LogicException`, потому что worker не сможет обновить такую задачу обратно.

### MarkExcelExportProcessing

Назначение:

Перевести задачу в `processing`, когда worker забирает ее в работу.

Command:

- `exportId`.

Result:

- `exportId`;
- `status`;
- `startedAt`.

### MarkExcelExportCompleted

Назначение:

Перевести задачу в `completed`, когда worker успешно создал и сохранил файл.

Command:

- `exportId`;
- `filePath`.

Result:

- `exportId`;
- `status`;
- `filePath`;
- `completedAt`.

### MarkExcelExportFailed

Назначение:

Перевести задачу в `failed`, когда worker не смог создать файл.

Command:

- `exportId`;
- `errorMessage`.

Result:

- `exportId`;
- `status`;
- `errorMessage`;
- `failedAt`.

## Internal API

Internal API отделен от пользовательского API.

Пользовательский API:

```text
/api/...
```

Worker API:

```text
/internal/...
```

### Получить pending задачи

```text
GET /internal/excel-exports/pending?limit=10
```

Response:

```json
{
  "data": {
    "exports": [
      {
        "id": 15,
        "status": "pending",
        "calculationResultPayload": {
          "tables": [],
          "metrics": {},
          "warnings": []
        },
        "createdAt": "2026-05-01T10:00:00+00:00"
      }
    ]
  }
}
```

### Взять задачу в обработку

```text
POST /internal/excel-exports/{exportId}/processing
```

Response:

```json
{
  "data": {
    "export": {
      "id": 15,
      "status": "processing",
      "startedAt": "2026-05-01T10:01:00+00:00"
    }
  }
}
```

### Завершить успешно

```text
POST /internal/excel-exports/{exportId}/completed
Content-Type: application/json
```

Request body:

```json
{
  "filePath": "exports/model-15.xlsx"
}
```

Response:

```json
{
  "data": {
    "export": {
      "id": 15,
      "status": "completed",
      "filePath": "exports/model-15.xlsx",
      "completedAt": "2026-05-01T10:02:00+00:00"
    }
  }
}
```

### Завершить ошибкой

```text
POST /internal/excel-exports/{exportId}/failed
Content-Type: application/json
```

Request body:

```json
{
  "errorMessage": "xlsx generation failed"
}
```

Response:

```json
{
  "data": {
    "export": {
      "id": 15,
      "status": "failed",
      "errorMessage": "xlsx generation failed",
      "failedAt": "2026-05-01T10:02:00+00:00"
    }
  }
}
```

## Ошибки internal API

Если export-задача не найдена:

```json
{
  "error": "not_found"
}
```

HTTP status: `404`.

Если статус задачи не позволяет выполнить переход:

```json
{
  "error": "invalid_state"
}
```

HTTP status: `409`.

Если body запроса некорректный:

```json
{
  "error": "invalid_payload"
}
```

HTTP status: `422`.

## Что реализовано

- guard-правила lifecycle в `ExcelExport`;
- repository методы `findById()` и `findPendingForProcessing()`;
- Doctrine implementation для worker queue;
- in-memory repository для unit-тестов;
- use case `GetPendingExcelExportsForProcessing`;
- use case `MarkExcelExportProcessing`;
- use case `MarkExcelExportCompleted`;
- use case `MarkExcelExportFailed`;
- internal controllers;
- `ExcelExportWorkerResponseFactory`;
- unit-тесты application layer и response factory.

## Что сознательно отложено

- авторизация internal API;
- service token/header для worker-а;
- race-condition lock при конкурентных worker-ах;
- retries;
- настоящий Go worker;
- генерация `.xlsx`;
- сохранение файла в storage;
- download endpoint для пользователя;
- polling или push-обновление статусов на frontend.

## Критерии проверки

```bash
docker compose exec php-fpm php bin/console debug:router internal.excel_export.pending
docker compose exec php-fpm php bin/console debug:router internal.excel_export.mark_processing
docker compose exec php-fpm php bin/console debug:router internal.excel_export.mark_completed
docker compose exec php-fpm php bin/console debug:router internal.excel_export.mark_failed
docker compose exec php-fpm php bin/console doctrine:schema:validate
docker compose exec php-fpm php bin/console lint:container
docker compose exec php-fpm php bin/phpunit
```

Ожидаемый результат:

- все internal routes зарегистрированы;
- container собирается;
- Doctrine schema синхронизирована;
- тесты зеленые;
- worker lifecycle закрыт на Symfony-стороне.
