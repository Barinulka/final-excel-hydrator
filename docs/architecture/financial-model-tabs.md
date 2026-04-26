# Financial Model Tabs

## Цель

Документ фиксирует текущий рабочий список вкладок редактора финансовой модели.

Список не считается окончательным. Он нужен как каркас для дальнейшей разработки блоков модели.

## Route

Основной route редактора:

```text
/projects/{projectShortId}/models/{financialModelShortId}/edit/{tabKey}
```

Пример:

```text
/projects/23456789ab/models/ab23456789/edit/input_params
```

Старый route:

```text
/projects/{projectShortId}/models/{financialModelShortId}/time-params
```

оставлен только как compatibility redirect на `edit/input_params`.

## Текущий список вкладок

| Order | Key | Label | Назначение |
|---:|---|---|---|
| 10 | `input_params` | Входные параметры | Сроки и базовые настройки модели. Сейчас включает TimeParams. |
| 20 | `initial_investments` | Первоначальные инвестиции | Инвестиции до запуска проекта. |
| 30 | `sales` | Продажи | Объемы продаж, цены, выручка. |
| 40 | `cost_of_sales` | Себестоимость | Прямые расходы и себестоимость продаж. |
| 50 | `expenses` | Затраты | Операционные расходы. |
| 60 | `payroll` | ФОТ | Персонал, зарплаты, начисления. |
| 70 | `working_capital` | Оборотный капитал | Запасы, дебиторка, кредиторка и связанные допущения. |
| 80 | `settings` | Настройки | Налоги, НДС, ставки и общие расчетные допущения. |
| 90 | `calculations` | Расчеты | Preview, таблицы, ключевые показатели. |
| 100 | `ai_analysis` | AI-анализ | Будущий AI-анализ модели. |
| 110 | `export` | Экспорт | Excel export и статус файлов. |

## Правила

- Вкладка регистрируется через `FinancialModelTabProviderInterface`.
- Каждая вкладка имеет `key`, `label`, `description`, `routeName`, `template`, `order`.
- Все вкладки используют route `app_financial_model_edit`.
- Twig layout не должен хранить список вкладок вручную.
- Controller не должен содержать business logic вкладки.
- Пока большинство вкладок являются placeholder templates.
