# Excel Export And Go Bridge (черновик)

## Цель документа
Документ фиксирует, как в новой версии проекта должен быть устроен Excel export и мост между Symfony и отдельным Go-сервисом.

Цель:
* не повторить controller-heavy flow из прототипа;
* не сделать Excel единственным местом, где "по-настоящему" считается модель;
* сохранить прагматичную и простую интеграцию с отдельным Go-сервисом;
* заложить архитектуру, которая позже выдержит summary, таблицы, графики, Excel export и AI report без разворота слоев.

## Главный принцип
Excel export является output channel.

Он не должен быть:
* единственным расчетным сценарием;
* источником правды по бизнес-состоянию модели;
* местом, где впервые собирается правильная финансовая логика.

Правильная цепочка:

`FinancialModel data -> calculation snapshot -> excel payload -> Go hydrator -> .xlsx file`

## Source Of Truth
Source of truth:
* PostgreSQL;
* сохраненные доменные блоки модели;
* backend calculation snapshot, построенный из сохраненного состояния.

Не являются source of truth:
* frontend state;
* Excel-файл;
* Go-сервис;
* AI-сервис.

## Роль Go-Сервиса
Go-сервис не является domain/calculation service.

Go-сервис является specialized workbook writer.

Он должен:
* принять transport payload;
* открыть Excel template или создать пустую книгу;
* записать значения, формулы, стили и validation rules;
* сохранить `.xlsx`;
* вернуть Symfony результат генерации.

Go-сервис не должен знать:
* что такое `Project`;
* что такое `FinancialModel`;
* как считаются `TimeParams`, `Investments` и будущие блоки;
* кто пользователь;
* какие у модели domain invariants.

## Общий Flow V1
Первая версия должна быть синхронной.

Flow:
1. пользователь нажимает `Выгрузить в Excel`;
2. controller определяет текущего пользователя и идентификаторы модели;
3. controller вызывает `ExportFinancialModelToExcel`;
4. use case находит модель и проверяет доступ;
5. use case строит calculation snapshot;
6. excel payload builder преобразует snapshot в transport payload для Go;
7. Symfony HTTP client вызывает Go-сервис;
8. Go-сервис генерирует `.xlsx`;
9. Symfony возвращает пользователю download flow.

## Разделение По Слоям

### Presentation Layer
Controller должен:
* принять request;
* определить текущего пользователя;
* вызвать use case;
* вернуть download response, redirect или JSON с данными о файле.

Controller не должен:
* строить calculation snapshot;
* собирать Excel payload;
* знать Excel coordinates;
* знать детали Go HTTP protocol сверх use case boundary.

### Application Layer
Главный use case:
* `ExportFinancialModelToExcel`

Use case должен:
* найти `FinancialModel` по `projectShortId + financialModelShortId + owner`;
* проверить доступ и состояние модели;
* получить расчетное представление модели;
* передать его в Excel payload builder;
* вызвать Go client;
* вернуть результат экспорта.

Use case не должен:
* знать Twig, Stimulus, DOM и CSS;
* сам писать HTTP response;
* сам знать детали Excel шаблона по ячейкам;
* напрямую работать с Docker, volume и файловой системой Go-сервиса.

### Calculation Layer
Перед экспортом backend должен построить reusable calculation snapshot.

Этот snapshot является общим источником для:
* summary;
* табличных представлений на вкладках;
* графиков;
* Excel export;
* будущего AI report / recommendations service.

Excel export не должен строить свою отдельную "особую" версию расчетов, если эти же расчеты нужны UI.

### Excel Payload Builder
Отдельный builder преобразует calculation snapshot в transport payload для Go.

Пример имени:
* `FinancialModelExcelPayloadBuilder`

Builder отвечает за:
* выбор template;
* структуру payload;
* sheets;
* cell values;
* formulas;
* styles;
* validation metadata;
* именованные технические conventions Excel-представления.

Builder не отвечает за:
* поиск модели в БД;
* права доступа;
* HTTP-вызов Go;
* бизнес-валидацию request.

### Infrastructure Layer
Отдельный infrastructure adapter вызывает Go-сервис.

Пример имени:
* `GoExcelHydratorClient`

Он отвечает за:
* `POST /generate`;
* сериализацию transport payload;
* обработку transport errors;
* маппинг ошибок в инфраструктурные исключения;
* возврат результата генерации.

## Calculation Snapshot И Excel Export
Excel export должен питаться не напрямую от entity-графа, а от расчетного снимка модели.

Это важно по двум причинам:
1. один и тот же backend calculation result должен быть переиспользуем в UI и Excel;
2. экспорт не должен становиться скрытым "вторым бэкендом" с отдельной логикой расчета.

На первом этапе snapshot может строиться на лету.

Если позже расчеты станут тяжелыми, можно отдельно решить:
* caching;
* async jobs;
* persisted calculation results.

Но это не должно ломать контракт Excel export.

## Контракт Symfony -> Go
Первая версия контракта должна быть минимальной.

Request:
* `template`
* `data`

`template` определяет Excel template или режим генерации.

`data` содержит transport-структуру для:
* листов;
* ячеек;
* значений;
* формул;
* styles;
* validations.

Response:
* `filename`

При необходимости позже можно расширить response:
* `path`
* `mimeType`
* `size`
* `warnings`

Но в первой версии это не обязательно.

## HTTP Endpoints Go-Сервиса
Минимальный API Go-сервиса:
* `GET /health`
* `POST /generate`

`/health` нужен для:
* Docker healthchecks;
* локальной диагностики;
* быстрой проверки связности Symfony -> Go.

`/generate` нужен для:
* приема transport payload;
* генерации Excel-файла;
* возврата результата генерации.

## Docker И Интеграция Между Сервисами
Для первой версии допустим pragmatic setup, аналогичный прототипу:
* отдельный сервис `excel-hydrator` в `compose.yaml`;
* `EXCEL_HYDRATOR_URL` в Symfony env;
* volume для готовых файлов;
* mounted templates directory для Go;
* HTTP-вызов Symfony -> Go по внутреннему docker hostname.

Это решение подходит для текущего этапа, потому что:
* просто диагностируется;
* не усложняет локальную разработку;
* не требует отдельного object storage сразу.

Переход к S3/object storage можно сделать позже отдельным этапом, если действительно появится потребность.

## Download Flow
На первом этапе допустим один из двух flow:

### Вариант A
Symfony получает `filename` от Go и отдает пользователю download route на готовый файл.

### Вариант B
Symfony получает `filename` и делает redirect на внутренний download endpoint.

Оба варианта допустимы.

Важно только, чтобы:
* пользователь не знал внутреннюю структуру Go-сервиса;
* controller не работал с файлами напрямую в обход use case boundary;
* файл не становился частью business state.

## Что Берем Из Прототипа Как Reference
Можно использовать как reference:
* отдельный Go-сервис;
* endpoints `/health` и `/generate`;
* HTTP bridge из Symfony;
* Docker Compose подход;
* shared output volume;
* `excelize` как Excel engine.

Не нужно переносить слепо:
* controller-heavy orchestration;
* смешение export flow и page logic;
* builder, который слишком рано становится местом бизнес-расчета;
* Excel-specific координаты и формулы, размазанные по application/controller слою.

## Рекомендуемая Структура В Новом Проекте

### Application
`src/Application/FinancialModel/ExportFinancialModelToExcel/`

Ожидаемые классы:
* `ExportFinancialModelToExcelCommand`
* `ExportFinancialModelToExcelHandler`
* `ExportFinancialModelToExcelResult`
* exceptions

### Calculation
Отдельный builder/query/use case для calculation snapshot.

Имя можно определить позже, но смысл должен быть один:
* summary;
* таблицы;
* charts;
* Excel export;
* AI output

используют общий backend calculation source.

### Infrastructure
`src/Infrastructure/Excel/`

Ожидаемые классы:
* `GoExcelHydratorClient`
* `HttpGoExcelHydratorClient`
* DTO/mapper для transport payload, если потребуется

### Presentation
Controller экспорта:
* web controller или API controller

Конкретный тип controller зависит от UX flow, но архитектурная роль не меняется:
controller только открывает use case.

## Ошибки И Границы Ответственности
Нужно разделять:

### Business/Application Errors
* модель не найдена;
* нет доступа;
* модель в недопустимом состоянии для экспорта;
* нет обязательных данных для экспорта.

### Infrastructure Errors
* Go-сервис недоступен;
* timeout;
* невалидный response;
* ошибка генерации файла;
* ошибка шаблона.

Controller не должен смешивать эти типы ошибок в одну безликую ошибку.

## Async Later, Not Now
В будущем экспорт можно перевести в async flow через Messenger:
* пользователь запускает экспорт;
* задача ставится в очередь;
* generation job выполняется отдельно;
* пользователь получает ссылку на готовый файл.

Но это не нужно в первой версии.

Сначала правильнее:
* зафиксировать contract;
* собрать sync export;
* подтвердить корректность payload-builder boundary;
* только потом решать, нужен ли async.

## Что Не Делаем
* Не считаем, что Excel export и есть расчетный движок.
* Не помещаем бизнес-расчеты в Go.
* Не строим export payload в controller.
* Не дублируем calculation logic отдельно для UI и отдельно для Excel.
* Не делаем object storage и async queue до появления реальной необходимости.
* Не делаем Excel шаблон источником бизнес-состояния.

## Следующий Практический Этап
Следующий этап реализации должен идти так:
1. зафиксировать use case `ExportFinancialModelToExcel`;
2. определить contract calculation snapshot для export;
3. спроектировать `GoExcelHydratorClient`;
4. добавить `excel-hydrator` в новый `compose.yaml`;
5. только после этого переносить или адаптировать Go-сервис из прототипа.
