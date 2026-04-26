# 05. CalculationResult и preview расчетов

## Цель

Ввести единый расчетный контракт `CalculationResult`.

Этот контракт должны использовать:

- frontend preview;
- будущий Excel export через Go;
- будущий AI-анализ;
- будущие snapshot-версии модели.

## Структура CalculationResult

```json
{
  "tables": [
    {
      "code": "timeline",
      "title": "Временная шкала",
      "periods": ["2026-01", "2026-02"],
      "rows": [
        {
          "code": "period_start_date",
          "title": "Начало месяца",
          "values": ["2026-01-01", "2026-02-01"]
        }
      ]
    }
  ],
  "metrics": {
    "period_count": 2
  },
  "warnings": []
}
```

## Текущая таблица `timeline`

Сейчас `CalculationResult` содержит одну таблицу:

```text
code: timeline
title: Временная шкала
```

Строки:

- `period_start_date` - начало месяца;
- `period_end_date` - окончание месяца;
- `investment_activity` - флаг инвестиционной деятельности;
- `operating_activity` - флаг операционной деятельности;
- `operating_start` - флаг начала операционной деятельности.

## Preview API

Endpoint:

```text
POST /api/projects/{projectShortId}/models/{financialModelShortId}/preview
```

Route name:

```text
api.financial_model.preview
```

Поведение:

- controller получает текущего пользователя;
- controller собирает `BuildFinancialModelCalculationQuery`;
- handler ищет модель текущего пользователя;
- handler строит `CalculationResult`;
- response factory превращает результат в JSON;
- при недоступной модели возвращается `404` и `{"error": "not_found"}`.

## Почему POST

Сейчас preview не требует request body.

POST выбран заранее, потому что позже preview сможет принимать незасейвленные изменения формы и строить предварительный расчет без сохранения всех вкладок в БД.

## Frontend

Вкладка:

```text
calculations / Расчеты
```

Поведение:

- Stimulus controller вызывает preview API;
- выводит warnings;
- выводит таблицы из `CalculationResult`;
- не выводит `metrics.period_count`, потому что количество периодов уже есть в общей сводке страницы;
- заголовки колонок таблицы показывают номера периодов `1`, `2`, `3`, ...
- даты периода показываются строками `Начало месяца` и `Окончание месяца`.

## Принятые решения

- `CalculationResult` не знает про HTTP.
- API response factory не считает данные, а только сериализует DTO.
- Controller не содержит расчетной логики.
- Frontend не пересчитывает финансовую модель.
- Frontend только отображает JSON, полученный от API.

## Критерии готовности

- PHPUnit проходит для calculation application tests.
- PHPUnit проходит для response factory.
- Symfony видит route `api.financial_model.preview`.
- Twig вкладки `Расчеты` валиден.
- JS controller не содержит синтаксических ошибок.
- В браузере вкладка `Расчеты` показывает таблицу временной шкалы.

