# 07. ExcelExport task в Symfony

## Цель

Добавить в Symfony сущность и application use case для создания задачи на Excel export.

На этом этапе Symfony не генерирует Excel-файл. Symfony только фиксирует намерение пользователя: "для этой финансовой модели нужно подготовить Excel export".

## Что реализовано

- enum статусов `ExcelExportStatus`;
- Doctrine entity `ExcelExport`;
- Doctrine migration для таблицы `excel_exports`;
- application repository interface `ExcelExportRepository`;
- Doctrine implementation `DoctrineExcelExportRepository`;
- use case `CreateExcelExport`;
- API endpoint для создания export-задачи;
- кнопка создания export-задачи в интерфейсе модели;
- unit-тест сущности;
- unit-тест application handler.

## Статусы

```text
pending
processing
completed
failed
```

Смысл статусов:

- `pending` - задача создана Symfony и ожидает обработки worker-ом;
- `processing` - worker забрал задачу в работу;
- `completed` - файл успешно создан и сохранен;
- `failed` - при генерации произошла ошибка.

## Сущность ExcelExport

Поля:

- `id`;
- `project`;
- `financialModel`;
- `status`;
- `calculationResultPayload`;
- `filePath`;
- `errorMessage`;
- `createdAt`;
- `updatedAt`;
- `startedAt`;
- `completedAt`;
- `failedAt`.

Связи:

- `project` - `ManyToOne` к `Project`;
- `financialModel` - `ManyToOne` к `FinancialModel`;
- обратные коллекции в `Project` и `FinancialModel` пока не добавляем.

Почему `ManyToOne`:

- у одной модели может быть несколько export-запросов;
- пользователь может запустить export повторно;
- в будущем можно хранить историю export-задач.

Почему храним и `project`, и `financialModel`:

- проще фильтровать export jobs по проекту;
- удобнее строить списки задач;
- worker-у проще выбирать pending jobs;
- задача фиксирует контекст модели на момент создания.

## Инварианты сущности

`ExcelExport::create(Project $project, FinancialModel $financialModel, array $calculationResultPayload)`:

- проверяет, что модель принадлежит указанному проекту;
- требует непустой `calculationResultPayload`;
- сохраняет snapshot расчета для будущего worker-а;
- ставит статус `pending`;
- не заполняет `startedAt`, потому что задача еще не обрабатывается;
- не заполняет `filePath`, `errorMessage`, `completedAt`, `failedAt`.

`markProcessing()`:

- ставит статус `processing`;
- заполняет `startedAt`.

`markCompleted(string $filePath)`:

- требует непустой `filePath`;
- trim-ит `filePath`;
- ставит статус `completed`;
- заполняет `completedAt`;
- очищает `errorMessage`.

`markFailed(string $errorMessage)`:

- требует непустой `errorMessage`;
- trim-ит `errorMessage`;
- ставит статус `failed`;
- заполняет `failedAt`;
- сохраняет `errorMessage`.

Публичных setters для `project`, `financialModel`, `status`, `filePath`, `errorMessage` нет. Состояние меняется только через осмысленные методы.

## Таблица БД

Таблица:

```text
excel_exports
```

Важные индексы:

```text
excel_exports__project_id__idx
excel_exports__financial_model_id__idx
excel_exports__status_created_at__idx
```

Индекс `(status, created_at)` нужен будущему worker-у для выборки очереди:

```sql
WHERE status = 'pending'
ORDER BY created_at ASC
```

## CreateExcelExport use case

Application flow:

1. Handler получает текущего пользователя, `projectShortId`, `financialModelShortId`.
2. Ищет модель через `FinancialModelRepository::findOneByShortIdForProjectAndOwner(...)`.
3. Если модель не найдена - кидает `FinancialModelForExcelExportNotFoundException`.
4. Если модель архивная - кидает `ArchivedFinancialModelCannotBeExportedException`.
5. Получает project из модели.
6. Строит `CalculationResult`.
7. Преобразует результат в JSON payload.
8. Создает `ExcelExport::create($project, $financialModel, $calculationResultPayload)`.
9. Сохраняет задачу через `ExcelExportRepository`.
10. Возвращает result:

```text
exportId
projectShortId
financialModelShortId
status
```

`exportId` пока nullable, потому что в unit-тесте нет flush и database-generated id.

## Что сознательно не делаем на этом этапе

- выдачу готового файла;
- Go worker;
- Messenger queue;
- генерацию Excel в PHP.

Список задач export во вкладке модели описан отдельным этапом: [08-excel-export-list.md](08-excel-export-list.md).
Snapshot `CalculationResult` описан отдельным этапом: [09-excel-export-calculation-snapshot.md](09-excel-export-calculation-snapshot.md).

## Критерии проверки

```bash
php bin/phpunit tests/Entity/ExcelExportTest.php
php bin/phpunit tests/Application/ExcelExport
php bin/phpunit
docker compose exec php-fpm php bin/console doctrine:schema:validate
```

Ожидаемый результат:

- тесты зеленые;
- Doctrine mapping корректный;
- БД синхронизирована с mapping;
- новая задача создается со статусом `pending`;
- архивную модель нельзя отправить на export.
