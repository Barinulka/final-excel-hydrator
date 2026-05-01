# 08. Список Excel export-задач

## Цель

Показать пользователю историю Excel export-задач для конкретной финансовой модели.

На этом этапе Symfony по-прежнему не генерирует Excel-файл. Интерфейс только показывает задачи, которые уже созданы, и их текущий статус.

## Пользовательский сценарий

1. Пользователь открывает вкладку `Экспорт` в редакторе модели.
2. Frontend делает `GET`-запрос за списком задач.
3. Если задач нет, показывается пустое состояние.
4. Пользователь нажимает `Выгрузить в excel`.
5. Symfony создает новую задачу со статусом `pending`.
6. Frontend обновляет список без перезагрузки страницы.
7. Новая задача появляется в таблице.

## API

### Получить список задач

```text
GET /api/projects/{projectShortId}/models/{financialModelShortId}/exports/excel
```

Успешный ответ:

```json
{
  "data": {
    "exports": [
      {
        "id": 15,
        "status": "pending",
        "filePath": null,
        "errorMessage": null,
        "createdAt": "2026-04-21T10:00:00+00:00",
        "startedAt": null,
        "completedAt": null,
        "failedAt": null
      }
    ]
  }
}
```

Если модель не найдена или не принадлежит текущему пользователю:

```json
{
  "error": "not_found"
}
```

HTTP status: `404`.

### Создать задачу

Создание задачи уже реализовано на том же URL, но другим HTTP-методом:

```text
POST /api/projects/{projectShortId}/models/{financialModelShortId}/exports/excel
```

## Application layer

Добавлен use case `GetExcelExportsForModel`.

Основные классы:

- `GetExcelExportsForModelQuery`;
- `GetExcelExportsForModelHandler`;
- `GetExcelExportsForModelResult`;
- `ExcelExportListItem`;
- `FinancialModelForExcelExportsNotFoundException`.

Flow handler-а:

1. Получить текущего пользователя, `projectShortId`, `financialModelShortId`.
2. Найти финансовую модель через `FinancialModelRepository::findOneByShortIdForProjectAndOwner(...)`.
3. Если модель не найдена - выбросить `FinancialModelForExcelExportsNotFoundException`.
4. Получить список задач через `ExcelExportRepository::findLatestForFinancialModel(...)`.
5. Преобразовать entity в простые DTO для API.

## Repository contract

В `ExcelExportRepository` добавлен метод:

```php
/**
 * @return list<ExcelExport>
 */
public function findLatestForFinancialModel(FinancialModel $financialModel): array;
```

Сортировка для UI: новые задачи выше старых.

Это отличается от будущей worker-очереди, где pending-задачи нужно будет брать в порядке `createdAt ASC`, то есть сначала самые старые.

## UI

Во вкладке `Экспорт` теперь есть таблица задач.

Колонки:

- статус;
- дата создания;
- дата начала обработки;
- дата завершения;
- дата ошибки;
- файл или сообщение.

Файлы:

- `templates/financial_model/tabs/_export.html.twig`;
- `assets/controllers/excel_export_list_controller.js`;
- `assets/controllers/excel_export_create_controller.js`;
- `assets/styles/pages/financial-model.css`.

После успешного создания задачи `excel_export_create_controller.js` отправляет browser event:

```text
excel-export:create-success
```

`excel_export_list_controller.js` слушает это событие и повторно загружает список задач.

## Принятые решения

- Список задач является read-only.
- Готовый файл пока нельзя скачать из UI.
- Автоматический polling статусов пока не добавляем.
- После создания задачи список обновляется один раз.
- Обратные коллекции `excelExports` в `Project` и `FinancialModel` пока не добавляем.
- Сортировка списка: `createdAt DESC`, потому что для пользователя важнее последняя созданная задача.

## Что сознательно отложено

- Go worker;
- перевод задач в `processing`, `completed`, `failed` реальным worker-ом;
- download endpoint для готового файла;
- кнопка скачивания файла;
- polling или Mercure/SSE для live-обновления статусов;
- хранение пути к файлу в реальном storage;
- генерация Excel.

## Критерии проверки

```bash
php bin/phpunit
docker compose exec php-fpm php bin/phpunit
php bin/console lint:twig templates/financial_model/tabs/_export.html.twig
php bin/console debug:router api.excel_export.list
php bin/console debug:router api.excel_export.create
```

Ожидаемый результат:

- тесты зеленые;
- Twig шаблон валиден;
- `GET` route списка зарегистрирован;
- `POST` route создания зарегистрирован;
- на вкладке `Экспорт` пустое состояние показывается, если задач нет;
- после нажатия `Выгрузить в excel` задача появляется в таблице без перезагрузки страницы.
