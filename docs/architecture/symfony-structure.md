# Symfony Structure (черновик)

## Цель документа
Документ фиксирует структуру Symfony-приложения перед началом реализации.

Цель структуры — сохранить доменные и application-границы, но не построить тяжелый enterprise-DDD. Symfony, Doctrine, Twig и Stimulus остаются рабочими инструментами, но не становятся местом проектирования бизнес-логики.

## Текущий контекст проекта
* Symfony 8.
* PHP 8.4+.
* Doctrine ORM 3.
* PostgreSQL.
* Twig, Asset Mapper, Stimulus и Turbo.
* Doctrine настроен на attribute mapping из `src/Entity`.

## Главный подход
Используем DDD-light / layered architecture:

* Domain Layer — бизнес-логика, value objects, enums, доменные правила.
* Application Layer — use cases, commands, DTO, orchestration, transaction boundary.
* Infrastructure Layer — Doctrine repositories, external services, id generation, Excel client.
* Presentation Layer — Symfony controllers, request parsing, response, Twig, API responses.

Контроллеры тонкие. Entity не должна превращаться в место, куда controller складывает raw request data. Twig и Stimulus не содержат бизнес-логику.

## Структура `src`

```text
src/
  Application/
    Project/
    FinancialModel/
    TimeParams/
    Investments/
    Shared/

  Domain/
    Project/
    FinancialModel/
    TimeParams/
    Investments/
    User/
    Shared/

  Entity/
    Project.php
    FinancialModel.php
    TimeParams.php
    InvestmentBlock.php
    InvestmentItem.php
    InvestmentSchedulePeriod.php
    User.php
    SiteSetting.php

  Infrastructure/
    Persistence/
      Doctrine/
        Repository/
    ShortId/
    Clock/
    Excel/

  Controller/
    Web/
    Api/

  Presentation/
    Web/
      Page/
      ViewModel/
      Tab/
    Api/
      Request/
      Response/
      Mapper/
```

## Почему Doctrine Entity остаются в `src/Entity`
На первом этапе Doctrine entities остаются в `src/Entity`, потому что текущая Symfony-конфигурация уже мапит `App\Entity` из `src/Entity`.

Это прагматичное решение:
* меньше конфигурации на старте;
* проще генерировать и поддерживать миграции;
* проще работать с Doctrine Bundle и Maker Bundle;
* достаточно для первого production slice.

Ограничение: наличие Doctrine entity в `src/Entity` не означает, что controller может напрямую применять к ней request data. Изменения состояния должны проходить через use cases.

Перенос Doctrine entities по модулям не планируется, пока текущая структура не начнет реально мешать.

## Entity Rules
Doctrine entity:
* хранит persisted state;
* содержит простые domain methods для изменения собственного состояния;
* защищает локальные инварианты, которые не требуют внешних зависимостей;
* использует PHP enums/value objects там, где это не усложняет Doctrine mapping чрезмерно;
* не знает HTTP, Twig, Stimulus, формы и API payload;
* не вызывает repositories, services, controllers или внешние клиенты.

Примеры допустимых методов:
* `Project::rename(string $title)`
* `FinancialModel::rename(string $title)`
* `FinancialModel::archive()`
* `FinancialModel::restore()`
* `TimeParams::update(...)`

Примеры недопустимых обязанностей:
* генерировать `shortId`;
* рассчитывать следующий `versionNumber` через запрос в БД;
* отправлять Excel export request;
* парсить HTTP request;
* собирать view model страницы.

## Domain Layer

### Назначение
Domain Layer содержит язык и правила предметной области.

### Что здесь живет
```text
src/Domain/Shared/ValueObject/
  ShortId.php
  Money.php
  YearMonth.php
  MonthDuration.php
  ModelPeriod.php

src/Domain/Project/Enum/
  ProjectStatus.php

src/Domain/FinancialModel/Enum/
  FinancialModelStatus.php
  AmountDisplayFormat.php

src/Domain/TimeParams/Enum/
  ForecastStep.php

src/Domain/Investments/Enum/
  InvestmentCategory.php
  InvestmentTreatment.php
  TaxabilityStatus.php
  PropertyTaxBase.php

src/Domain/User/Enum/
  UserRole.php
```

### Правила
* Domain Layer не зависит от Symfony.
* Domain Layer не зависит от Doctrine repositories.
* Value objects не должны знать о request/response.
* Enums отражают code-defined множества, важные для бизнес-логики.

## Application Layer

### Назначение
Application Layer реализует use cases из `docs/architecture/use-cases.md`.

### Рекомендуемый стиль именования
Каждый state-changing use case получает отдельную папку:

```text
src/Application/FinancialModel/CreateFinancialModel/
  CreateFinancialModelCommand.php
  CreateFinancialModelHandler.php
  CreateFinancialModelResult.php

src/Application/TimeParams/UpdateTimeParams/
  UpdateTimeParamsCommand.php
  UpdateTimeParamsHandler.php
  UpdateTimeParamsResult.php
```

Для query/page use cases:

```text
src/Application/FinancialModel/ViewFinancialModelPage/
  ViewFinancialModelPageQuery.php
  ViewFinancialModelPageHandler.php
```

### Что здесь живет
* command/query DTO;
* use case handlers;
* application-level validation orchestration;
* transaction boundary;
* вызовы repositories;
* вызовы domain services;
* вызовы infrastructure ports;
* сбор application result DTO.

### Что здесь не живет
* Symfony Request/Response;
* Twig templates;
* Stimulus behavior;
* Doctrine QueryBuilder, если это можно спрятать за repository;
* HTML/view-specific details.

## Repository Boundaries

### Интерфейсы
Repository interfaces размещаются рядом с application/domain смыслом, а реализации — в Infrastructure.

```text
src/Application/Project/ProjectRepository.php
src/Application/FinancialModel/FinancialModelRepository.php
src/Application/TimeParams/TimeParamsRepository.php

src/Infrastructure/Persistence/Doctrine/Repository/DoctrineProjectRepository.php
src/Infrastructure/Persistence/Doctrine/Repository/DoctrineFinancialModelRepository.php
src/Infrastructure/Persistence/Doctrine/Repository/DoctrineTimeParamsRepository.php
```

Для первого slice можно держать repository interfaces в `Application`, потому что именно use cases определяют, какие persistence operations нужны.

### Правила
* Controller не использует Doctrine repositories напрямую для бизнес-операций.
* Use case работает с repository interface.
* Doctrine implementation скрывает QueryBuilder/EntityManager.
* Специфичные read queries для page builder можно выделять отдельно от write repositories.

## Infrastructure Layer

### Назначение
Infrastructure Layer содержит технические реализации.

### Что здесь живет
```text
src/Infrastructure/Persistence/Doctrine/
  Repository/

src/Infrastructure/ShortId/
  RandomShortIdGenerator.php

src/Infrastructure/Clock/
  SystemClock.php

src/Infrastructure/Excel/
  ExcelExportClient.php
```

### ShortId
`ShortIdGenerator` должен быть dependency use cases.

Правила:
* генерирует 10 lowercase-символов;
* использует алфавит `23456789abcdefghjkmnpqrstuvwxyz`;
* не зависит от title;
* не гарантирует уникальность сам по себе;
* uniqueness гарантируется БД и retry-логикой use case.

### Transactions
State-changing use cases должны выполняться в transaction boundary.

На первом этапе используем небольшой `TransactionalRunner`, который внутри вызывает Doctrine EntityManager transaction.

`TransactionalRunner` нужен, чтобы:
* не размазывать EntityManager transaction по handlers;
* одинаково обрабатывать state-changing use cases;
* позже добавить retry для `unique violation`, если это потребуется;
* упростить тестирование application handlers.

## Presentation Layer

### Controllers
Контроллеры остаются в `src/Controller`, чтобы не менять текущую Symfony routing-конфигурацию на старте.

Рекомендуемая структура:

```text
src/Controller/Web/
  ProjectController.php
  FinancialModelController.php

src/Controller/Api/
  FinancialModel/
    TimeParamsController.php
```

Controller обязан:
* получить Request;
* определить текущего User;
* преобразовать вход в command/query DTO;
* вызвать use case;
* вернуть RedirectResponse, JsonResponse или Response.

Controller не должен:
* применять DTO к entity;
* рассчитывать versionNumber;
* генерировать shortId;
* выполнять финансовые расчеты;
* собирать сложный view context вручную;
* знать детали всех вкладок FinancialModel.

### Request / Response DTO
HTTP-specific DTO размещаются в Presentation:

```text
src/Presentation/Api/Request/
src/Presentation/Api/Response/
```

Application command/query DTO размещаются в Application.

Это разделение нужно, чтобы use case не зависел от HTTP-формата.

### Entity Boundary / Action Models

Doctrine entity не прокидывается целиком туда, где нужен только небольшой набор данных для конкретного действия или отображения.

Entity используется:
* внутри domain methods;
* внутри application use case после загрузки из repository;
* при сохранении через repository;
* в unit-тестах домена и use cases.

Entity не используется как:
* API response;
* Twig view model;
* Stimulus payload;
* Excel export payload;
* AI/report payload;
* универсальный объект для передачи данных между слоями.

Для каждого направления данных используем объект с явной ролью:
* `Request` / `RequestDTO` — данные, пришедшие по HTTP;
* `Command` — намерение выполнить application action;
* `Query` — запрос на чтение/application view;
* `Result` — результат выполнения use case;
* `Response` / `ResponseDTO` — данные API-ответа;
* `ViewModel` — данные, подготовленные для Twig;
* `ReadModel` — плоское представление для списков, таблиц и read-only экранов;
* `CalculationSnapshot` — расчетное представление FinancialModel;
* `ExcelExportPayload` — данные для Excel export.

Не используем общий суффикс `Model` для таких объектов, если он не является частью бизнес-языка. `FinancialModel` допустимо, потому что это доменный термин. `TimeParamsModel`, `ProjectModel`, `UpdateSomethingModel` не используем: по названию неясно, это entity, DTO, command, view model или расчетный объект.

Важно: эти объекты не должны дублировать доменную бизнес-логику. Они передают данные, а инварианты остаются в domain entity/value objects и application use cases.

## Page Builder / View Model

Для сложных страниц используем page builder.

Page builders живут в Presentation Layer, потому что они собирают модель отображения для Twig, а не выполняют бизнес-операцию.

```text
src/Presentation/Web/Page/Project/
  ProjectPageBuilder.php
  ProjectPageViewModel.php

src/Presentation/Web/Page/FinancialModel/
  FinancialModelPageBuilder.php
  FinancialModelPageViewModel.php
```

Page builder собирает:
* page title;
* данные Project;
* данные FinancialModel;
* status/read-only flags;
* список вкладок;
* active tab;
* view model активной вкладки;
* доступные actions.

Twig получает подготовленную view model и не принимает бизнес-решения.

Project workspace проектируется отдельно в `docs/architecture/project-workspace.md`.

Для Project workspace используем Twig + Turbo Frames + Stimulus:
* `/projects` — вход в workspace;
* `/projects/{projectShortId}` — workspace с выбранным Project;
* sidebar и content находятся внутри одного Turbo Frame `project_workspace`;
* при выборе Project backend заново собирает view model, а Turbo заменяет workspace frame;
* frontend не является source of truth для выбранного Project.

## Tab Registry

FinancialModel tabs являются code-defined features.

Рекомендуемая структура:

```text
src/Presentation/Web/Tab/
  FinancialModelTabDefinition.php
  FinancialModelTabProviderInterface.php
  FinancialModelTabRegistry.php

src/Presentation/Web/Tab/Provider/
  TimeParamsTabProvider.php
  InvestmentsTabProvider.php
```

Tab provider описывает:
* key;
* label;
* route name;
* template;
* order;
* required permission/status behavior;
* factory/builder для view model вкладки, если нужно.

Registry собирает providers и отдает список вкладок page builder-у.

Twig не должен хардкодить список вкладок.

## API-first Flow For Tabs

Финансовые вкладки сохраняются через JSON API.

Flow:
* Twig рендерит page shell и initial view model.
* Stimulus собирает payload.
* Controller преобразует JSON request в request DTO.
* Request DTO валидируется Symfony Validator.
* Request DTO преобразуется в application command.
* Use case применяет изменения.
* API возвращает response DTO.

Symfony Form не является основным механизмом для financial tabs.

## First Slice Implementation Scope

Первый vertical slice должен включать:
* User/security foundation, если авторизация еще не настроена;
* Project entity + repository + create/rename/page use cases;
* FinancialModel entity + repository + create/rename/archive/restore/delete/copy use cases;
* TimeParams entity + repository + update use case;
* ShortId generator;
* tab registry с минимумом вкладок;
* page builder для Project page и FinancialModel page;
* минимальные controllers;
* миграции для users/projects/financial_models/time_params.

Не включать в первый slice:
* CAPEX UI;
* Investments migrations, если они не нужны для первого работающего slice;
* Excel export integration, если нет готового контракта;
* charts/summary endpoints;
* admin panel;
* сложную permission model.

## Investments Implementation Scope

Investments не начинаем до завершения first slice.

Когда дойдем до Investments:
* добавляем migrations для `investment_blocks`, `investment_items`, `investment_schedule_periods`;
* добавляем Investments use cases;
* добавляем API endpoints;
* добавляем одну минимальную категорию/flow;
* только потом расширяем категории.

## Calculation And Output Services

Расчетное представление FinancialModel должно развиваться как backend calculation snapshot.

Snapshot может использоваться для:
* UI-таблиц на вкладках;
* summary;
* chart endpoints;
* Excel export;
* будущего AI report/recommendations service.

Excel service и AI service являются output services. Они не являются source of truth и не должны владеть бизнес-состоянием FinancialModel.

На текущем этапе calculation services могут быть PHP-классами внутри Symfony. Отдельный calculation microservice не вводим до появления реальной нагрузки или отдельного расчетного контракта.

## Tests

Минимальный набор тестов для first slice:
* unit tests для value objects: `ShortId`, `YearMonth`, `MonthDuration`;
* unit tests для TimeParams derived timeline;
* application tests для `CreateFinancialModel`;
* application tests для `UpdateTimeParams`;
* application tests для archive/restore/delete FinancialModel;
* repository/migration smoke tests, если тестовая БД настроена.

## Naming Rules
* Использовать доменно точные имена: `FinancialModel`, `TimeParams`, `InvestmentBlock`.
* Не использовать generic names вроде `Model`, `BlockData`, `TabData`.
* Не называть классы по UI-деталям: `ModalCreateModelService`, `TabFormHandler`.
* `DTO` suffix использовать только для DTO, если он реально пересекает layer boundary.
* Use case handler называть по действию: `CreateFinancialModelHandler`, `UpdateTimeParamsHandler`.

## Resolved Decisions
* Doctrine entities остаются в `src/Entity`. Перенос по модулям не планируется, пока текущая структура не начнет реально мешать.
* State-changing use cases выполняются через небольшой `TransactionalRunner`.
* Page builders живут в Presentation Layer и собирают view models для Twig.
* Excel service contract не входит в first slice и будет спроектирован отдельным этапом.
* На первом slice используем один repository interface на агрегат. Отдельные read repositories добавляем позже, если page builders начнут требовать сложные выборки.
* Backend calculation snapshot является основой для UI-таблиц, summary, charts, Excel export и будущего AI report.
