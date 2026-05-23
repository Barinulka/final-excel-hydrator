# Application Use Cases

## Статус

Документ частично устарел после удаления `Project`.

Актуальная базовая схема:

```text
User -> FinancialModel
```

Старые use cases `CreateProject`, `RenameProject`, `ViewProjectPage` и операции с `projectShortId` больше не являются частью текущего продукта.

Для новых use cases используем правило доступа:

```text
financialModelShortId + current user
```

При дальнейшей работе этот документ нужно переписать под актуальные use cases:

* `CreateFinancialModel`;
* `UpdateFinancialModelDetails`;
* `ArchiveFinancialModel`;
* `RestoreFinancialModel`;
* `DeleteFinancialModel`;
* `UpdateTimeParams`;
* `BuildFinancialModelCalculation`;
* `CreateExcelExport`;
* `GetExcelExportsForModel`;
* `DownloadExcelExport`.

Ниже оставлен исторический черновик раннего этапа.

## Цель документа
Документ описывает application use cases до проектирования routes, controllers, API endpoints, Twig и Stimulus.

Use case описывает бизнес-операцию приложения: кто вызывает действие, какие данные нужны, какие проверки выполняются, какие сущности меняются, какой результат возвращается и какие ошибки возможны.

## Principles
* Use cases живут в Application Layer.
* Controller не применяет request data напрямую к entity.
* Controller получает request, определяет текущего пользователя, вызывает use case и возвращает response.
* Вход HTTP преобразуется в request DTO или command DTO до вызова use case.
* Doctrine entity не используется как HTTP response, Twig view model, Excel payload или AI/report payload.
* Для передачи данных между слоями используются объекты с явной ролью: Request, Command, Query, Result, Response, ViewModel, ReadModel, CalculationSnapshot, Payload.
* Use case работает с repository boundaries, domain objects и value objects.
* Use case не знает Twig, Stimulus, CSS, modal, toast и DOM.
* Use case не должен собирать сложный view context вручную.
* State-changing use cases выполняются в transaction boundary.
* Генерация `shortId` является application dependency.
* Уникальность `shortId` защищается БД, а use case повторяет генерацию при collision.
* Query/page use cases не изменяют состояние.
* Расчеты выполняются backend-ом на основе сохраненного состояния.
* Расчетное представление модели должно быть reusable: UI tables, summary, charts, Excel export и будущий AI report используют backend calculation data.
* Excel export является output channel, а не единственным расчетным движком.
* Financial tabs являются code-defined application features.

## Common Errors
* `Unauthenticated` — пользователь не авторизован.
* `AccessDenied` — пользователь не имеет доступа к ресурсу.
* `NotFound` — ресурс не найден или недоступен пользователю.
* `ValidationFailed` — входные данные не прошли валидацию.
* `ResourceArchived` — операция запрещена для архивного ресурса.
* `InvalidState` — операция невозможна из-за текущего состояния сущности.
* `ShortIdGenerationFailed` — не удалось сгенерировать уникальный `shortId` после допустимого числа попыток.

## First Slice Use Cases

### CreateProject

#### Actor
Авторизованный пользователь.

#### Input
* `title`
* `description` optional

#### Checks
* пользователь авторизован
* `title` не пустой
* `description` допустимой длины, если заполнен
* можно сгенерировать уникальный `shortId`

#### State Changes
* создается Project
* Project получает `ownerId` текущего пользователя
* Project получает сгенерированный `shortId`
* Project получает статус `active`
* сохраняются `title`, `description`, `createdAt`, `updatedAt`

#### Result
* `projectId`
* `projectShortId`
* `title`

#### Errors
* `Unauthenticated`
* `ValidationFailed`
* `ShortIdGenerationFailed`

### RenameProject

#### Actor
Авторизованный пользователь, владелец Project.

#### Input
* `projectShortId`
* `title`

#### Checks
* Project существует
* пользователь владеет Project
* Project находится в статусе `active`
* `title` не пустой

#### State Changes
* Project получает новое `title`
* обновляется `updatedAt`
* `shortId` не меняется

#### Result
* обновленное `title`

#### Errors
* `Unauthenticated`
* `NotFound`
* `AccessDenied`
* `ResourceArchived`
* `ValidationFailed`

### ViewProjectPage

#### Actor
Авторизованный пользователь, владелец Project.

#### Input
* `projectShortId`

#### Checks
* Project существует
* пользователь владеет Project

#### State Changes
Нет.

#### Result
View model страницы Project:
* Project summary
* список FinancialModel
* активные FinancialModel идут перед архивными
* архивные FinancialModel помечены как read-only/archived
* доступные действия по каждой FinancialModel
* данные для create FinancialModel flow

#### Errors
* `Unauthenticated`
* `NotFound`
* `AccessDenied`

### CreateFinancialModel

#### Actor
Авторизованный пользователь, владелец Project.

#### Input
* `projectShortId`
* `investmentStartMonth`
* `investmentDurationMonths`
* `commercialOperationDurationMonths`
* `forecastStep`

#### Checks
* Project существует
* пользователь владеет Project
* Project находится в статусе `active`
* `investmentStartMonth` задан как месяц и год
* `investmentStartMonth` может быть нормализован в первое число месяца
* `investmentDurationMonths` положительный
* `commercialOperationDurationMonths` положительный
* `forecastStep` соответствует `ForecastStep`
* следующий `versionNumber` может быть рассчитан внутри Project
* расчет `versionNumber` выполняется transaction-safe: через lock или retry при unique violation
* можно сгенерировать уникальный `shortId`

#### State Changes
* создается FinancialModel внутри Project
* FinancialModel получает сгенерированный `shortId`
* FinancialModel получает следующий `versionNumber`
* FinancialModel получает auto-generated `title` на основе Project title и `versionNumber`
* FinancialModel получает статус `active`
* создается обязательный TimeParams
* сохраняются `createdAt`, `updatedAt`

#### Result
* `financialModelId`
* `financialModelShortId`
* `projectShortId`
* `title`
* `versionNumber`
* target для перехода к редактированию FinancialModel

#### Errors
* `Unauthenticated`
* `NotFound`
* `AccessDenied`
* `ResourceArchived`
* `ValidationFailed`
* `ShortIdGenerationFailed`

### RenameFinancialModel

#### Actor
Авторизованный пользователь, владелец Project.

#### Input
* `projectShortId`
* `financialModelShortId`
* `title`

#### Checks
* Project существует
* FinancialModel существует внутри Project
* пользователь владеет Project
* FinancialModel находится в статусе `active`
* `title` не пустой

#### State Changes
* FinancialModel получает новое `title`
* обновляется `updatedAt`
* `shortId` не меняется
* `versionNumber` не меняется

#### Result
* обновленное `title`

#### Errors
* `Unauthenticated`
* `NotFound`
* `AccessDenied`
* `ResourceArchived`
* `ValidationFailed`

### ArchiveFinancialModel

#### Actor
Авторизованный пользователь, владелец Project.

#### Input
* `projectShortId`
* `financialModelShortId`

#### Checks
* Project существует
* FinancialModel существует внутри Project
* пользователь владеет Project
* FinancialModel находится в статусе `active`

#### State Changes
* FinancialModel получает статус `archived`
* обновляется `updatedAt`

#### Result
* `financialModelShortId`
* `status = archived`

#### Errors
* `Unauthenticated`
* `NotFound`
* `AccessDenied`
* `InvalidState`

### RestoreFinancialModel

#### Actor
Авторизованный пользователь, владелец Project.

#### Input
* `projectShortId`
* `financialModelShortId`

#### Checks
* Project существует
* FinancialModel существует внутри Project
* пользователь владеет Project
* FinancialModel находится в статусе `archived`

#### State Changes
* FinancialModel получает статус `active`
* обновляется `updatedAt`

#### Result
* `financialModelShortId`
* `status = active`

#### Errors
* `Unauthenticated`
* `NotFound`
* `AccessDenied`
* `InvalidState`

### DeleteArchivedFinancialModel

#### Actor
Авторизованный пользователь, владелец Project.

#### Input
* `projectShortId`
* `financialModelShortId`

#### Checks
* Project существует
* FinancialModel существует внутри Project
* пользователь владеет Project
* FinancialModel находится в статусе `archived`

#### State Changes
* FinancialModel удаляется окончательно
* связанные TimeParams удаляются cascade
* связанные InvestmentBlock, InvestmentItem и InvestmentSchedulePeriod удаляются cascade, если они существуют
* скопированные модели, которые ссылались на удаляемую модель через `sourceModelId`, не удаляются
* `sourceModelId` у скопированных моделей становится null

#### Result
* признак успешного удаления

#### Errors
* `Unauthenticated`
* `NotFound`
* `AccessDenied`
* `InvalidState`

### CopyFinancialModel

#### Actor
Авторизованный пользователь, владелец Project.

#### Input
* `projectShortId`
* `sourceFinancialModelShortId`

#### Checks
* Project существует
* source FinancialModel существует внутри Project
* пользователь владеет Project
* source FinancialModel находится в статусе `active`
* следующий `versionNumber` может быть рассчитан внутри Project
* расчет `versionNumber` выполняется transaction-safe: через lock или retry при unique violation
* можно сгенерировать уникальный `shortId`

#### State Changes
* создается новая FinancialModel внутри того же Project
* новая FinancialModel получает сгенерированный `shortId`
* новая FinancialModel получает следующий `versionNumber`
* новая FinancialModel получает auto-generated `title` на основе Project title и `versionNumber`
* новая FinancialModel получает статус `active`
* `sourceModelId` новой модели указывает на source FinancialModel
* TimeParams копируются как независимые данные
* Investments копируется как независимый блок, если он существует
* изменения source FinancialModel после копирования не влияют на новую модель

#### Result
* `financialModelId`
* `financialModelShortId`
* `projectShortId`
* `title`
* `versionNumber`
* target для перехода к редактированию новой FinancialModel

#### Errors
* `Unauthenticated`
* `NotFound`
* `AccessDenied`
* `ResourceArchived`
* `ValidationFailed`
* `ShortIdGenerationFailed`

### UpdateTimeParams

#### Actor
Авторизованный пользователь, владелец Project.

#### Input
* `projectShortId`
* `financialModelShortId`
* `investmentStartMonth`
* `investmentDurationMonths`
* `commercialOperationDurationMonths`
* `forecastStep`

#### Checks
* Project существует
* FinancialModel существует внутри Project
* пользователь владеет Project
* FinancialModel находится в статусе `active`
* TimeParams существует
* `investmentStartMonth` задан как месяц и год
* `investmentStartMonth` может быть нормализован в первое число месяца
* `investmentDurationMonths` положительный
* `commercialOperationDurationMonths` положительный
* `forecastStep` соответствует `ForecastStep`
* производный расчетный горизонт не пустой

#### State Changes
* обновляется TimeParams
* `investmentStartMonth` сохраняется как первое число месяца
* обновляется `updatedAt` у TimeParams
* обновляется `updatedAt` у FinancialModel
* данные других доменных блоков не удаляются и не переписываются автоматически

#### Result
* обновленные TimeParams
* производные даты: investmentEndDate, commercialOperationStartDate, commercialOperationEndDate, modelEndDate
* предупреждение, если существующие данные других блоков требуют проверки после изменения расчетного горизонта

#### Errors
* `Unauthenticated`
* `NotFound`
* `AccessDenied`
* `ResourceArchived`
* `ValidationFailed`

### ViewFinancialModelPage

#### Actor
Авторизованный пользователь, владелец Project.

#### Input
* `projectShortId`
* `financialModelShortId`
* `activeTab`

#### Checks
* Project существует
* FinancialModel существует внутри Project
* пользователь владеет Project
* `activeTab` существует в code-defined tab registry

#### State Changes
Нет.

#### Result
View model страницы FinancialModel:
* page title
* Project summary
* FinancialModel summary
* status FinancialModel
* read-only flag для archived FinancialModel
* список вкладок из tab registry
* active tab
* template/view model активной вкладки
* доступные действия по модели
* export action

#### Errors
* `Unauthenticated`
* `NotFound`
* `AccessDenied`
* `ValidationFailed`

### ExportFinancialModelToExcel

#### Actor
Авторизованный пользователь, владелец Project.

#### Input
* `projectShortId`
* `financialModelShortId`

#### Checks
* Project существует
* FinancialModel существует внутри Project
* пользователь владеет Project
* FinancialModel находится в допустимом состоянии для export
* TimeParams существует
* обязательные данные для export валидны
* если Investments содержит InvestmentItem, у строк, участвующих в расчете, заполнен график финансирования

#### State Changes
Нет в domain state модели.

Допустим технический side effect:
* внешний output service генерирует Excel-файл на основе transport payload

#### Application Flow
* use case находит `FinancialModel`
* use case строит calculation snapshot
* Excel payload builder преобразует snapshot в transport payload
* infrastructure client вызывает Go Excel hydrator service
* use case возвращает descriptor готового файла

Application use case не возвращает готовый HTTP stream и не формирует download response. Это ответственность controller/presentation layer.

#### Result
* `filename`
* file descriptor/result для download flow
* metadata, если нужно: `mimeType`, `size`, `warnings`

#### Errors
* `Unauthenticated`
* `NotFound`
* `AccessDenied`
* `ValidationFailed`
* `InvalidState`
* `ExportFailed`

## Later Use Cases

### InitializeInvestmentBlock
Создает пустой InvestmentBlock для FinancialModel, если блок еще не существует.

### AddInvestmentItem
Добавляет InvestmentItem в code-defined категорию Investments.

### UpdateInvestmentItem
Обновляет поля InvestmentItem с учетом правил обязательности выбранной категории.

### DeleteInvestmentItem
Удаляет InvestmentItem и его schedule periods.

### UpdateInvestmentSchedule
Сохраняет отмеченные месяцы финансирования InvestmentItem.

### ValidateFinancialModelForCalculation
Проверяет готовность модели к расчету, summary, charts или Excel export.

### BuildFinancialModelSummary
Строит первый backend summary для FinancialModel.

#### Actor
Авторизованный пользователь, владелец Project.

#### Input
* `projectShortId`
* `financialModelShortId`

#### Checks
* Project существует
* FinancialModel существует внутри Project
* пользователь владеет Project
* TimeParams существует

#### State Changes
Нет.

#### Result
* Project metadata
* FinancialModel metadata
* TimeParams summary
* Timeline summary
* `timeline.periods`
* warnings

`timeline.periods` используется UI для Excel-like таблицы временного ряда:
* месяцы идут по горизонтали;
* показатели идут строками;
* длинный горизонт скроллится по горизонтали;
* frontend отображает готовые даты и флаги, но не пересчитывает их.

#### Errors
* `Unauthenticated`
* `NotFound`
* `AccessDenied`
* `InvalidState`

### BuildFinancialModelCalculationSnapshot
Строит backend calculation snapshot FinancialModel для UI-таблиц, summary, charts, Excel export и будущих output-сервисов.

#### Actor
Вызывается другим application use case или query handler.

#### Input
* `projectShortId`
* `financialModelShortId`
* `owner`

#### Checks
* Project существует
* FinancialModel существует внутри Project
* пользователь владеет Project
* обязательные блоки модели существуют
* сохраненные данные модели находятся в состоянии, достаточном для нужного вида расчета

#### State Changes
Нет.

#### Result
Calculation snapshot, который может включать:
* Project metadata
* FinancialModel metadata
* TimeParams input data
* Timeline
* summary indicators
* chart data
* validation warnings
* export-ready calculation data

#### Errors
* `Unauthenticated`
* `NotFound`
* `AccessDenied`
* `InvalidState`

### GenerateAiModelReport
Будущий use case для формирования AI-отчета и рекомендаций на основе calculation snapshot. AI-сервис не изменяет FinancialModel напрямую без отдельного пользовательского действия.

### UpdateSiteSettings
Позволяет admin/superAdmin изменять presentation/content-настройки приложения.

### UpdateFinancialModelAmountDisplayFormat
Позволяет пользователю выбрать формат отображения сумм для конкретной FinancialModel: `wholeRubles` или `decimalRubles`.

## Open Questions
* Нужно ли в будущем разрешать копирование FinancialModel между разными Project?
* Где будет храниться ставка НДС после появления полноценного Taxation block?
* Как именно quarter/year должны агрегировать данные, когда будет реализована поддержка ForecastStep?
