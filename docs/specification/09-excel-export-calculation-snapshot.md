# 09. Snapshot CalculationResult для Excel export

## Цель

Сохранять готовый `CalculationResult` внутри `ExcelExport` в момент создания export-задачи.

Это делает export-задачу самодостаточной: будущий Go worker сможет читать готовый JSON и генерировать Excel-файл, не зная бизнес-логику финансовой модели.

## Главное правило

Go worker не пересчитывает финансовую модель.

Расчет делает Symfony. Worker получает snapshot результата и отвечает только за генерацию `.xlsx`, сохранение файла и обновление статуса export-задачи.

## Почему нужен snapshot

Без snapshot-а задача export была бы неполной: у нее есть статус и связь с моделью, но нет данных, из которых worker должен построить Excel.

Snapshot решает несколько задач:

- фиксирует расчет на момент нажатия `Выгрузить в excel`;
- отделяет расчетную бизнес-логику Symfony от Go worker;
- дает будущему worker-у стабильный JSON-контракт;
- готовит основу для AI-анализа и версионирования расчетов.

## Контракт payload

В `ExcelExport` добавлено поле:

```text
calculationResultPayload
```

Тип Doctrine:

```php
#[ORM\Column(type: Types::JSON)]
private array $calculationResultPayload = [];
```

Формат payload:

```json
{
  "tables": [
    {
      "code": "timeline",
      "title": "Временная шкала",
      "periods": ["2026-04", "2026-05"],
      "rows": [
        {
          "code": "investment_activity",
          "title": "Инвестиционная деятельность",
          "values": [1, 1]
        }
      ]
    }
  ],
  "metrics": {
    "period_count": 30
  },
  "warnings": []
}
```

Важно: в snapshot не добавляется API-обертка `data`.

API response может иметь формат:

```json
{
  "data": {}
}
```

Но persisted snapshot должен быть чистым контрактом:

```text
tables
metrics
warnings
```

## Что реализовано

- `ExcelExport` хранит `calculationResultPayload`;
- `ExcelExport::create(...)` требует непустой payload;
- `CreateExcelExportHandler` строит `CalculationResult` перед созданием задачи;
- `CalculationResultPayloadFactory` превращает DTO расчета в простой array;
- создана миграция для колонки `calculation_result_payload`;
- старые строки `excel_exports` получают пустой валидный snapshot;
- обновлены unit-тесты entity и application layer.

## Application flow

`CreateExcelExportHandler`:

1. Получает текущего пользователя, `projectShortId`, `financialModelShortId`.
2. Ищет финансовую модель через `FinancialModelRepository`.
3. Проверяет, что модель не архивная.
4. Получает project из модели.
5. Запускает `BuildFinancialModelCalculationHandler`.
6. Передает `CalculationResult` в `CalculationResultPayloadFactory`.
7. Создает `ExcelExport` с payload.
8. Сохраняет задачу через `ExcelExportRepository`.
9. Возвращает короткий result для API.

Controller при этом остается тонким.

## Миграция

Для существующих строк нельзя сразу добавить `NOT NULL` JSON-колонку без значения.

Поэтому миграция добавляет временный default:

```sql
ALTER TABLE excel_exports
ADD calculation_result_payload JSON DEFAULT '{"tables":[],"metrics":{},"warnings":[]}'::json NOT NULL;
```

После добавления колонки default убирается:

```sql
ALTER TABLE excel_exports
ALTER calculation_result_payload DROP DEFAULT;
```

Новые export-задачи должны получать payload только через application logic.

## Что сознательно отложено

- Go worker;
- download endpoint;
- генерация Excel-файла;
- polling статуса на frontend.

Worker lifecycle и internal API описаны отдельным этапом: [10-excel-export-worker-bridge.md](10-excel-export-worker-bridge.md).

## Критерии проверки

```bash
docker compose exec php-fpm php bin/console doctrine:migrations:migrate --no-interaction
docker compose exec php-fpm php bin/console doctrine:schema:validate
docker compose exec php-fpm php bin/phpunit
```

Ожидаемый результат:

- миграция применяется;
- Doctrine schema синхронизирована;
- тесты зеленые;
- при создании export-задачи в `excel_exports.calculation_result_payload` сохраняется JSON с ключами `tables`, `metrics`, `warnings`.
