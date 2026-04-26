# 04. Временные параметры

## Цель

Описать расчетный горизонт финансовой модели.

Временные параметры нужны всем будущим расчетным блокам: инвестициям, продажам, затратам, ФОТ, налогам, preview, Excel export и AI-анализу.

## Пользовательские поля

Поля формы:

- дата начала инвестиций;
- длительность инвестиций в месяцах;
- длительность коммерческой работы в месяцах;
- шаг прогнозирования.

## Правила ввода

Дата начала инвестиций:

- пользователь выбирает месяц и год;
- день не вводится;
- внутри системы всегда фиксируется первое число выбранного месяца;
- формат API: `YYYY-MM`.

Длительность инвестиций:

- целое положительное число;
- единица измерения: месяц.

Длительность коммерческой работы:

- целое положительное число;
- единица измерения: месяц.

Шаг прогнозирования:

- значения в UI: `мес.`, `кв.`, `год`;
- значения в API/enum: `month`, `quarter`, `year`;
- сейчас шаг сохраняется, но не агрегирует временной ряд.

## Расчетные даты

Дата начала инвестиций:

```text
investmentStartDate = первое число выбранного месяца
```

Дата окончания инвестиционной фазы:

```text
investmentEndDate = конец месяца(investmentStartDate + investmentDurationMonths - 1 месяц)
```

Excel-эквивалент:

```text
EOMONTH(D4,D5-1)
```

Дата начала коммерческой эксплуатации:

```text
commercialOperationStartDate = investmentEndDate + 1 день
```

Excel-эквивалент:

```text
D9+1
```

Дата окончания коммерческой фазы:

```text
commercialOperationEndDate = конец месяца(commercialOperationStartDate + commercialOperationDurationMonths - 1 месяц)
```

Excel-эквивалент:

```text
EOMONTH(D10,D6-1)
```

## Временной ряд

Временной ряд сейчас строится помесячно.

Для каждого периода:

- `periodNumber` - номер периода с 1;
- `yearMonth` - месяц периода в формате `YYYY-MM`;
- `periodStartDate` - первое число месяца;
- `periodEndDate` - последнее число месяца.

Первый период:

```text
periodStartDate = investmentStartDate
periodEndDate = конец месяца(periodStartDate)
```

Следующие периоды:

```text
periodStartDate = конец предыдущего периода + 1 день
periodEndDate = конец месяца(periodStartDate)
```

Количество периодов:

```text
investmentDurationMonths + commercialOperationDurationMonths
```

## Флаги

Флаг инвестиционной деятельности:

```text
periodStartDate >= investmentStartDate
AND periodEndDate <= investmentEndDate
```

Флаг операционной деятельности:

```text
periodStartDate >= commercialOperationStartDate
```

Флаг начала операционной деятельности:

```text
periodStartDate == commercialOperationStartDate
```

## Сверка с Excel-примером

Для входных данных:

```text
Дата начала инвестиций: 2026-02
Длительность инвестиций: 24 месяца
Длительность коммерческой работы: 24 месяца
Шаг прогнозирования: месяц
```

Ожидаемый результат:

```text
investmentStartDate = 2026-02-01
investmentEndDate = 2028-01-31
commercialOperationStartDate = 2028-02-01
commercialOperationEndDate = 2030-01-31
periodCount = 48
```

## Что пока отложено

- Агрегация временного ряда по кварталам и годам.
- Влияние `forecastStep` на колонки расчетных таблиц.
- Полноценная Excel-выгрузка через Go worker.

## Критерии готовности

- Временная шкала совпадает с Excel-формулами.
- Модель не создается без TimeParams.
- Обновление TimeParams пересчитывает summary и preview.
- Расчет покрыт unit-тестами, включая кейс из Excel-примера.

