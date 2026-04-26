# 06. Excel export и границы Go worker

## Цель

Зафиксировать границы ответственности между Symfony и будущим Go worker для Excel export.

## Главное правило

Excel не генерируется в PHP.

Не используем:

- PhpSpreadsheet;
- генерацию Excel в Symfony controller;
- генерацию Excel в PHP service;
- синхронный export внутри HTTP request.

## Ответственность Symfony

Symfony отвечает за:

- авторизацию пользователя;
- проекты и модели;
- ввод и валидацию данных;
- расчет `CalculationResult`;
- создание задачи на Excel export;
- хранение статуса export;
- выдачу ссылки на готовый файл;
- API для чтения данных и статусов.

## Ответственность Go worker

Go worker будет отвечать за:

1. Найти pending ExcelExport jobs.
2. Пометить job как processing.
3. Загрузить подготовленный `CalculationResult` JSON.
4. Сгенерировать `.xlsx`.
5. Сохранить файл в настроенное хранилище.
6. Пометить export как completed.
7. При ошибке пометить export как failed.

## Важное ограничение

Go worker не пересчитывает финансовую модель.

Он получает уже подготовленный `CalculationResult` и только превращает его в Excel-файл.

## Будущая сущность ExcelExport

Планируемые поля:

- `id`;
- `project`;
- `financialModel`;
- `status`: `pending`, `processing`, `completed`, `failed`;
- `filePath`;
- `errorMessage`;
- `createdAt`;
- `startedAt`;
- `completedAt`;
- `failedAt`.

Планируемые методы:

- `markProcessing()`;
- `markCompleted(string $filePath)`;
- `markFailed(string $errorMessage)`;
- `isCompleted()`.

## Статус реализации

На текущем этапе реализован не Excel export, а основа для него:

- `CalculationResult`;
- preview API;
- таблица временной шкалы;
- сериализация результата в JSON.

Следующий слой export должен использовать этот контракт, а не создавать отдельную расчетную модель для Excel.

