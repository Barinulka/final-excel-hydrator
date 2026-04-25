# Calculation And Outputs (черновик)

## Главный принцип
Excel export не является единственным расчетным движком и не должен быть единственным местом, где существуют правильные финансовые расчеты.

Backend должен уметь строить расчетное представление FinancialModel на основе сохраненных данных модели.

Это расчетное представление дальше используется разными output-каналами:
* таблицы на вкладках;
* summary;
* графики;
* Excel export;
* будущий AI report/recommendations service.

## Source Of Truth
Source of truth для исходных данных:
* PostgreSQL;
* domain storage model;
* сохраненные данные Project, FinancialModel, TimeParams, Investments и будущих блоков.

Frontend не является source of truth.

Excel-файл не является source of truth.

AI-сервис не является source of truth.

## Calculation Snapshot
Calculation snapshot — это backend-сформированное расчетное представление FinancialModel.

Он может включать:
* исходные данные модели;
* производные даты TimeParams;
* расчетный временной ряд;
* расчеты по Investments;
* summary indicators;
* данные для графиков;
* validation warnings;
* данные для Excel export;
* данные для AI report.

Calculation snapshot не обязан храниться в БД на первом этапе. Его можно строить на лету из сохраненных исходных данных.

Если позже расчеты станут тяжелыми, можно будет добавить cache, async jobs или persisted calculation results отдельным архитектурным решением.

## FinancialModel Summary
`FinancialModelSummary` — первая прикладная форма calculation snapshot для UI/API.

Он строится backend-ом на лету из:
* FinancialModel;
* TimeParams;
* TimelineCalculator;
* будущих расчетных блоков модели.

На текущем этапе summary включает:
* Project metadata;
* FinancialModel metadata;
* TimeParams input summary;
* Timeline summary;
* `timeline.periods`;
* warnings.

`timeline.periods` содержит помесячный расчетный ряд:
* номер периода;
* месяц;
* начало месяца;
* окончание месяца;
* флаг инвестиционной деятельности;
* флаг операционной деятельности;
* флаг начала операционной деятельности.

Месячный ряд не хранится в БД. Он пересчитывается backend-ом из актуальных TimeParams.

Frontend не строит временной ряд самостоятельно. UI получает готовый `timeline.periods` из summary API и только отображает его.

## Output Channels

### UI Tables
На вкладках пользователь может видеть расчетные таблицы рядом с формами ввода.

Flow:
* пользователь меняет данные вкладки;
* frontend отправляет JSON request;
* backend сохраняет данные;
* backend пересчитывает нужную часть модели;
* backend возвращает данные для таблицы/summary/warnings;
* frontend отображает результат.

Таблицы, похожие на Excel, должны строиться по backend calculation data.

Пример для TimeParams:
* строки — показатели: начало месяца, окончание месяца, инвестиционная деятельность, операционная деятельность, начало операционной деятельности;
* колонки — месячные периоды из `timeline.periods`;
* горизонтальный scroll допустим и предпочтителен для длинного горизонта;
* frontend не пересчитывает даты и флаги, а только форматирует готовые значения.

### Summary And Analytics
Сводная вкладка должна строиться backend-ом на основе calculation snapshot.

Frontend получает уже подготовленные:
* summary cards;
* таблицы;
* chart data;
* предупреждения;
* ключевые показатели.

Frontend не собирает summary из DOM и не пересчитывает финансовые показатели самостоятельно.

### Charts
Chart endpoints должны возвращать готовые данные для графиков:
* labels;
* series;
* units;
* formatting metadata, если нужно.

Графики не должны строиться путем чтения данных из HTML-таблиц.

### Excel Export
Excel export — это output channel.

Он должен использовать сохраненные данные модели и/или calculation snapshot.

Excel может содержать формулы, но backend architecture не должна зависеть от того, что только Excel способен посчитать модель.

Базовый export flow:
* controller принимает пользовательское действие и вызывает use case;
* backend строит calculation snapshot модели;
* отдельный Excel payload builder преобразует snapshot в transport payload;
* Symfony вызывает отдельный Go-сервис через infrastructure client;
* Go-сервис гидратирует workbook и сохраняет `.xlsx`;
* Symfony возвращает пользователю download flow.

Go-сервис не является расчетным движком модели. Он является specialized output service для генерации Excel-файла.

Подробная схема этого потока зафиксирована в `docs/architecture/excel-export-architecture.md`.

### AI Report / Recommendations
Будущий AI-сервис должен получать структурированный model snapshot, а не читать Excel, Twig, DOM или сырые frontend-формы.

Ожидаемый input для AI-сервиса:
* Project summary;
* FinancialModel summary;
* TimeParams;
* заполненные доменные блоки;
* расчетные показатели;
* warnings/validation results;
* chart/summary data, если нужно.

AI-сервис может возвращать:
* текстовый отчет;
* рекомендации;
* риски;
* вопросы к пользователю;
* предложения по улучшению модели.

AI-сервис не должен изменять FinancialModel напрямую без отдельного use case и явного действия пользователя.

## Symfony Role
Symfony остается центральным application backend:
* принимает JSON requests;
* валидирует input;
* сохраняет данные;
* запускает use cases;
* строит calculation snapshot;
* отдает данные для UI/API;
* передает snapshot во внешние output services.

Если отдельный сервис нужен для Excel или AI, он подключается как specialized output service, а не как источник бизнес-состояния.

## Stateless Direction
Стремимся к stateless request flow:
* не храним данные финансовых вкладок в PHP session;
* не храним промежуточное состояние wizard-flow в session;
* каждый API request содержит payload, достаточный для своей операции;
* backend загружает актуальное состояние из БД;
* backend применяет use case;
* backend возвращает результат.

Бизнес-состояние хранится в PostgreSQL. Это нормально и не противоречит stateless API flow.

## Что Не Делаем
* Не делаем Excel единственным расчетным движком.
* Не считаем финансовую модель в Stimulus.
* Не строим summary из DOM.
* Не заставляем AI-сервис читать Excel как основной источник данных.
* Не храним financial tab state в session.
* Не вводим отдельный calculation microservice до появления реальной необходимости.

## Когда Возможен Отдельный Calculation Service
Отдельный calculation service может понадобиться позже, если:
* расчеты станут тяжелыми по CPU;
* потребуется долгий async calculation job;
* потребуется масштабировать расчетный движок отдельно от Symfony;
* появятся сложные сценарии симуляций/оптимизации.

На текущем этапе calculation services могут быть обычными PHP-классами внутри Symfony.
