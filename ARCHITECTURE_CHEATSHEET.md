# Architecture Cheatsheet

## Главная идея
Мы делим приложение не ради красоты, а чтобы не получить контроллеры на 500 строк и бизнес-логику в Twig/Stimulus.

Простой принцип:

```text
Controller принимает запрос.
Application use case выполняет действие.
Domain хранит правила и смысл.
Infrastructure работает с БД/внешними сервисами.
Presentation готовит ответ пользователю.
```

## Слои

## Domain
Domain — это смысл системы.

Здесь лежат вещи, которые были бы актуальны даже без Symfony, HTTP, Twig и PostgreSQL:

```text
src/Domain/
```

Примеры:
* `FinancialModelStatus`
* `ForecastStep`
* `ShortId`
* `Money`
* `YearMonth`
* `MonthDuration`

Domain отвечает на вопросы:
* что такое `FinancialModel`;
* какие статусы допустимы;
* что такое корректный `ShortId`;
* что такое положительная длительность в месяцах;
* какие значения может иметь `ForecastStep`.

Domain не должен знать:
* `Request`;
* `Response`;
* Controller;
* route;
* Twig;
* SQL-запросы;
* Go/Excel service.

Если класс из `Domain` импортирует `Symfony\Component\HttpFoundation\Request`, `EntityManagerInterface`, `Twig` или Controller, это почти всегда ошибка.

## Application
Application — это слой пользовательских действий.

```text
src/Application/
```

Здесь лежат use cases:
* `CreateFinancialModel`
* `UpdateTimeParams`
* `ArchiveFinancialModel`
* `CopyFinancialModel`

Application отвечает на вопросы:
* что нужно сделать, когда пользователь создает финансовую модель;
* какие сущности загрузить;
* какие проверки выполнить;
* какой `versionNumber` назначить;
* когда создать `TimeParams`;
* когда сохранить изменения;
* какой результат вернуть.

Типичный use case:

```text
CreateFinancialModelCommand
CreateFinancialModelHandler
CreateFinancialModelResult
```

Application может знать:
* domain objects;
* value objects;
* repository interfaces;
* transaction runner;
* shortId generator interface;
* current user id, если передали его в command.

Application не должен знать:
* Symfony Request;
* Twig;
* Stimulus;
* HTML;
* CSS;
* конкретный Doctrine EntityManager, если можно спрятать за repository/transaction runner;
* как именно выглядит JSON response.

Правильная мысль:

```text
CreateFinancialModelHandler создает FinancialModel и TimeParams.
```

Неправильная мысль:

```text
FinancialModelController сам создает entity, считает versionNumber, генерирует shortId и flush делает прямо внутри action.
```

## Infrastructure
Infrastructure — это техническая реализация.

```text
src/Infrastructure/
```

Здесь лежит то, что можно заменить без изменения смысла системы:
* `DoctrineFinancialModelRepository`
* `RandomShortIdGenerator`
* `DoctrineTransactionalRunner`
* `SystemClock`
* `ExcelExportClient`

Infrastructure отвечает на вопросы:
* как найти FinancialModel через Doctrine;
* как открыть transaction;
* как сгенерировать случайный shortId;
* как потом сходить в Go/Excel service.

Infrastructure может знать:
* Doctrine;
* EntityManager;
* DBAL exceptions;
* HTTP client;
* filesystem;
* внешние сервисы.

Infrastructure не должна тащить технические детали наверх. Например use case не должен зависеть от `EntityManagerInterface`, если мы договорились использовать repository + transaction runner.

## Presentation
Presentation — это слой интерфейса.

```text
src/Presentation/
src/Controller/
templates/
assets/controllers/
```

Presentation отвечает на вопросы:
* какой route вызвать;
* как распарсить HTTP request;
* какой JSON вернуть;
* какую Twig-страницу отрендерить;
* какие поля показать;
* какая вкладка активна;
* какие кнопки доступны;
* как собрать page view model.

Presentation может знать:
* Symfony Request/Response;
* Twig;
* routes;
* form input;
* JSON request/response DTO;
* page builders;
* tab registry.

Presentation не должна:
* считать финансовую модель;
* решать, как генерировать `versionNumber`;
* менять entity напрямую;
* валидировать доменные инварианты только на фронте;
* хранить бизнес-логику в Stimulus.

## Entity
Doctrine entities лежат отдельно:

```text
src/Entity/
```

Это persisted state. В Symfony это нормально.

Entity может:
* хранить поля;
* иметь простые методы изменения состояния;
* защищать локальные инварианты.

Например:

```php
$financialModel->rename($title);
$financialModel->archive();
$financialModel->restore();
```

Entity не должна:
* сама искать следующий `versionNumber`;
* сама генерировать `shortId`;
* сама сохранять себя;
* ходить в репозиторий;
* знать Request;
* знать Twig;
* знать Excel export.

## Типичный запрос
Пример: пользователь создает финансовую модель.

```text
HTTP request
  ↓
Controller
  ↓
Request DTO validation
  ↓
CreateFinancialModelCommand
  ↓
CreateFinancialModelHandler
  ↓
FinancialModelRepository / ShortIdGenerator / TransactionalRunner
  ↓
Entity: FinancialModel + TimeParams
  ↓
DB
  ↓
CreateFinancialModelResult
  ↓
Controller
  ↓
JSON response или redirect
```

Controller здесь только переводчик между HTTP и use case.

## Repository
Repository — это граница между application и БД.

В application:

```text
FinancialModelRepository
TimeParamsRepository
```

Это интерфейсы: что нужно use case от хранилища.

В infrastructure:

```text
DoctrineFinancialModelRepository
DoctrineTimeParamsRepository
```

Это реализация: как именно сделать это через Doctrine.

Use case зависит от интерфейса, не от Doctrine напрямую.

## DTO / Command / Result
Не передаем `Request` в use case.

Плохо:

```php
$handler->handle($request);
```

Хорошо:

```php
$command = new CreateFinancialModelCommand(
    userId: $user->getId(),
    title: $requestDto->title,
    description: $requestDto->description,
    investmentStartMonth: $requestDto->investmentStartMonth,
    investmentDurationMonths: $requestDto->investmentDurationMonths,
    commercialOperationDurationMonths: $requestDto->commercialOperationDurationMonths,
    forecastStep: $requestDto->forecastStep,
);

$result = $handler->handle($command);
```

Request DTO — про HTTP input.

Command DTO — про application action.

Result DTO — про результат use case.

Response DTO — про API output.

## Entity Не Уходит Наружу
Doctrine entity не передаем целиком туда, где нужен минимальный набор данных под конкретное действие или экран.

Entity нормально использовать:
* внутри use case;
* внутри domain methods;
* при сохранении через repository;
* в тестах домена/use case.

Entity не используем как:
* JSON response;
* Twig context;
* Stimulus payload;
* Excel export payload;
* AI/report payload.

Вместо абстрактных `SomethingModel` используем названия по роли:
* `CreateFinancialModelCommand`;
* `UpdateTimeParamsRequest`;
* `UpdateTimeParamsResult`;
* `TimeParamsResponse`;
* `FinancialModelPageViewModel`;
* `FinancialModelListReadModel`;
* `FinancialModelCalculationSnapshot`;
* `ExcelExportPayload`.

Исключение: `FinancialModel` — нормальное имя entity, потому что это бизнес-термин. А вот `ProjectModel`, `TimeParamsModel`, `UpdateTimeParamsModel` не используем: по названию непонятно, что это за объект.

Правило ревью: если объект называется `Model`, нужно проверить, нельзя ли назвать его точнее: `Command`, `Request`, `Response`, `ViewModel`, `ReadModel`, `Snapshot` или `Payload`.

## Page Builder
Page builder нужен, чтобы controller не собирал огромный массив для Twig.

Плохо:

```php
return $this->render('model/show.html.twig', [
    'project' => $project,
    'model' => $model,
    'tabs' => $tabs,
    'activeTab' => $activeTab,
    'exportUrl' => $exportUrl,
    'readonly' => $readonly,
]);
```

Лучше:

```php
$page = $financialModelPageBuilder->build($query);

return $this->render('financial_model/page.html.twig', [
    'page' => $page,
]);
```

Page builder живет в Presentation, потому что он собирает модель отображения.

## Tab Registry
Вкладки модели не должны быть hardcoded в Twig.

Плохо:

```twig
<a href="/time-params">Временные параметры</a>
<a href="/investments">Инвестиции</a>
```

Лучше:

```text
TimeParamsTabProvider
InvestmentsTabProvider
FinancialModelTabRegistry
```

Twig получает готовый список вкладок.

Это важно, потому что вкладка — application feature:
* route;
* template;
* permissions;
* API;
* validation;
* use case;
* storage model.

## Где что лежит

```text
src/Domain
```

Чистые правила и типы.

```text
src/Application
```

Use cases: что делает система.

```text
src/Infrastructure
```

Doctrine, transactions, id generator, внешние сервисы.

```text
src/Presentation
```

Page builders, view models, API request/response mappers.

```text
src/Controller
```

Symfony controllers, тонкие входные точки.

```text
src/Entity
```

Doctrine persisted state.

```text
templates
```

Twig, только отображение.

```text
assets/controllers
```

Stimulus, только browser interaction.

## Расчеты и output-каналы
Excel export — это output channel, а не единственный расчетный движок.

Правильное направление:

```text
saved model data
  ↓
backend calculation snapshot
  ↓
UI tables / summary / charts / Excel / AI report
```

Неправильное направление:

```text
saved model data
  ↓
Excel formulas as only real calculation
  ↓
UI and AI depend on Excel
```

Frontend показывает результаты, но не является source of truth.

AI-сервис в будущем должен получать structured calculation snapshot, а не читать Excel, Twig или DOM.

## Как решить, куда положить класс
Спроси себя:

Этот класс имел бы смысл без Symfony и БД?

Да — возможно `Domain`.

Этот класс описывает пользовательское действие?

Да — `Application`.

Этот класс работает с Doctrine, HTTP client, filesystem, random generator?

Да — `Infrastructure`.

Этот класс готовит данные для страницы/API?

Да — `Presentation`.

Это Symfony route/action?

Да — `Controller`.

Это хранится в БД?

Да — `Entity`.

## Главные запреты
Не делать:

```text
Controller -> Entity setters -> flush
```

для бизнес-операций.

Не делать:

```text
Twig решает, какие вкладки существуют.
```

Не делать:

```text
Stimulus считает финансовую модель.
```

Не делать:

```text
Все данные вкладок в financial_model.data JSON.
```

Не делать:

```text
Один AppService на все: ProjectService, который умеет всё.
```

## Практическое правило для первого slice
Если сомневаешься, делай проще, но не ломай границы:

* один use case = одно пользовательское действие;
* один handler = одна операция;
* один repository interface на агрегат;
* entity с минимальными методами;
* controller только вызывает handler;
* Twig только показывает view model;
* Stimulus только отправляет JSON и обновляет UI.

Это и есть наш DDD-light: достаточно строго, чтобы проект рос, но без архитектурной бюрократии.
