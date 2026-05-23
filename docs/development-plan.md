# Учебный план разработки

## Актуальный статус

Первые этапы плана были составлены до изменения требований.

Сейчас сущность `Project` удалена, актуальная схема:

```text
User -> FinancialModel
```

Старые этапы про `Project` ниже оставлены как история обучения и разработки. Актуальный переход зафиксирован в `docs/specification/15-models-without-projects-transition.md`.

## Подход

Проект развиваем как API-ориентированный модульный монолит на Symfony.

Не используем академическую DDD-подачу. Рабочая схема простая:

```text
Controller
  -> Application Service / Handler
    -> Entity + Repository
      -> Database
```

Роли слоев:

- `Controller` принимает HTTP-запрос, достает текущего пользователя, вызывает handler и возвращает ответ.
- `Request DTO` описывает входные данные API.
- `Handler / Application Service` выполняет конкретное действие приложения.
- `Entity` хранит состояние и простые правила самой сущности.
- `Repository` достает и сохраняет сущности.
- `Calculation service` считает модель и возвращает `CalculationResult`.

Контроллеры не содержат бизнес-логику. Excel не генерируется в PHP. Go-сервис отвечает только за создание `.xlsx` на основе подготовленного результата расчета.

Работаем по одному этапу за раз. На каждом этапе сначала разбираем идею, затем выполняем небольшую задачу, после этого код проходит ревью.

## Этап 1. Docker-окружение и baseline

Цель: получить стабильную локальную среду, в которой Symfony, PostgreSQL и тесты запускаются одинаково.

Что изучим:

- `compose.yaml`;
- PHP-FPM;
- Nginx;
- PostgreSQL;
- env-переменные;
- миграции;
- базовые Symfony-команды в Docker.

Файлы:

- `compose.yaml`;
- `compose.override.yaml`;
- `docker/Dockerfile`;
- `docker/nginx.conf`;
- `.env`;
- `.env.test`;
- README-раздел по запуску.

Результат: проект поднимается через Docker, Symfony отвечает в браузере, БД доступна, миграции применяются, тесты проходят.

Проверка:

- `docker compose up -d`;
- `bin/console doctrine:migrations:migrate`;
- `bin/phpunit`;
- открытие приложения на локальном порту.

## Этап 2. Уточнение целевой доменной модели

Цель: привести `Project` и `FinancialModel` к новой схеме без сложного DDD.

Что изучим:

- Doctrine entity design;
- enum-поля;
- UUID;
- timestamp-поля;
- простые методы сущностей;
- границы ответственности entity.

Файлы:

- `src/Entity/Project.php`;
- `src/Entity/FinancialModel.php`;
- enum-классы в `src/Domain/.../Enum`;
- новая миграция.

Результат: сущности соответствуют целевой схеме: `owner`, `name`, `description`, `currency`, `status`, `scenarioType`, `archivedAt`.

Проверка:

- миграции;
- unit-тесты методов сущностей;
- `doctrine:schema:validate`.

## Этап 3. Project use cases и API

Цель: оформить создание, просмотр, обновление и архивирование проектов через application layer.

Что изучим:

- command/result DTO;
- handler;
- repository interface;
- тонкий controller;
- validation request DTO.

Файлы:

- `src/Application/Project/...`;
- `src/Controller/Api/Project/...`;
- `src/Presentation/Api/Request/Project/...`;
- repository interfaces;
- Doctrine repository implementations.

Результат: API проекта не содержит бизнес-логику в контроллерах.

Проверка:

- unit-тесты handlers;
- request DTO tests;
- ручные запросы через curl/Postman.

## Этап 4. FinancialModel use cases и связь с Project

Цель: создать полноценный сценарий модели внутри проекта.

Что изучим:

- связь `ManyToOne`;
- проверку владельца через проект;
- enum `scenarioType`;
- статусы `draft`, `active`, `archived`.

Файлы:

- `src/Application/FinancialModel/Create...`;
- `src/Application/FinancialModel/Rename...`;
- `src/Application/FinancialModel/Archive...`;
- `src/Application/FinancialModel/Activate...`;
- API controllers;
- tests.

Результат: пользователь может создать несколько моделей внутри проекта, переименовать, активировать и архивировать модель.

Проверка:

- handler tests;
- Doctrine relation checks;
- API happy/error paths.

## Этап 5. Архитектура вкладок редактора модели

Цель: заложить 11 вкладок редактора без реализации всех расчетов сразу.

Что изучим:

- page builder;
- tab registry;
- Twig composition;
- разделение HTML-страницы и API.

Файлы:

- `src/Presentation/Web/Tab/...`;
- `src/Presentation/Web/Page/FinancialModel/...`;
- `templates/financial_model/page.html.twig`;
- заглушки templates для вкладок.

Результат: `/projects/{projectId}/models/{modelId}/edit` показывает редактор с вкладками.

Проверка:

- открытие страницы;
- проверка активной вкладки;
- отсутствие бизнес-логики в Twig/controller.

## Этап 6. TimeParams как первый настоящий блок модели

Цель: сделать вкладку "Временные параметры" образцом для будущих вкладок.

Что изучим:

- отдельную entity блока;
- update use case;
- API save через Stimulus;
- backend-calculated preview.

Файлы:

- `TimeParams` entity;
- TimeParams use cases;
- API request/mapper/controller;
- Stimulus controller;
- Twig partial.

Результат: временные параметры сохраняются через API, summary/preview строится backend-ом.

Проверка:

- unit-тесты `TimelineCalculator`;
- handler tests;
- ручная проверка UI.

## Этап 7. CalculationResult v1

Цель: ввести центральный контракт расчетного результата.

Что изучим:

- DTO-контракт;
- calculation service;
- отделение расчета от Excel/UI/AI.

Файлы:

- `src/Application/Calculation/...` или `src/Domain/Calculation/...`;
- DTO `CalculationResult`;
- DTO `CalculationTable`;
- DTO `CalculationRow`;
- builder/service расчета.

Результат: backend умеет вернуть mock/реальный `CalculationResult` по модели.

Проверка:

- `POST /api/projects/{projectId}/models/{modelId}/preview`;
- JSON соответствует контракту.

## Этап 8. Preview API и frontend-таблицы

Цель: использовать `CalculationResult` для live preview.

Что изучим:

- Stimulus `fetch`;
- JSON rendering;
- backend-owned calculations.

Файлы:

- preview controller;
- response factory;
- Stimulus controller;
- Twig preview tab.

Результат: вкладка "Расчеты / Preview" показывает таблицы из `CalculationResult`.

Проверка:

- изменение TimeParams обновляет preview;
- frontend не считает финансовую модель самостоятельно.

## Этап 9. ExcelExport entity и async export flow

Цель: реализовать Symfony-часть Excel export без генерации Excel в PHP.

Что изучим:

- job entity;
- статусы;
- API command;
- lifecycle methods;
- polling endpoint.

Файлы:

- `src/Entity/ExcelExport.php`;
- `ExcelExportStatus` enum;
- application use cases;
- API controllers;
- migration.

Результат: нажатие "Export to Excel" создает `ExcelExport` со статусом `pending`.

Проверка:

- API возвращает `exportId`;
- запись появляется в БД;
- статус можно получить отдельным endpoint.

## Этап 10. Go worker skeleton

Цель: добавить заготовку будущего Go-сервиса без преждевременной реализации Excel.

Что изучим:

- ответственность отдельного worker/service;
- границу Symfony/Go;
- почему Go не считает финансовую модель.

Файлы:

- `go-services/excel-worker/README.md`;
- позже, при необходимости, `go-services/excel-worker/main.go`;
- Docker placeholders.

Результат: в README зафиксирован будущий flow worker:

1. Find pending ExcelExport jobs.
2. Mark job as processing.
3. Load CalculationResult JSON.
4. Generate XLSX file.
5. Save file to configured storage.
6. Mark export as completed.
7. On error, mark export as failed.

Проверка:

- наличие документации;
- согласованный contract;
- отсутствие PHP Excel generation.

## Этап 11. Минимальная интеграция Symfony и Go

Цель: подготовить технический мост, когда Go worker уже сможет обновлять статусы.

Что изучим:

- service-to-service interaction;
- shared storage;
- polling;
- failure handling.

Файлы:

- compose service для worker;
- env-переменные;
- infrastructure client или command endpoint для worker;
- storage config.

Результат: Go-side skeleton может читать job и обновлять статус через API или БД-адаптер. Финальное решение выберем отдельно.

Проверка:

- fake worker переводит export из `pending` в `completed` или `failed`.

## Этап 12. AI-ready snapshot

Цель: подготовить `CalculationResult` и input snapshot для будущего AI-анализа и AI-помощника по заполнению вкладок.

Что изучим:

- structured context;
- read-only analysis flow;
- безопасное применение AI-предложений через отдельные use cases.

Файлы:

- snapshot DTO;
- AI analysis placeholder use case;
- tab placeholder.

Результат: AI не читает Excel/DOM, а получает структурированный backend snapshot.

Проверка:

- mock endpoint возвращает AI-ready payload на основе модели.
