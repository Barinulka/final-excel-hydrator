# 15. Переход к моделям без Project

## Статус

Это не завершенный этап, а зафиксированное изменение требований от заказчика.

Документ нужен, чтобы после паузы вернуться к работе без потери контекста.

## Новое концептуальное решение

Сущность `Project` больше не нужна.

Было:

```text
User -> Project -> FinancialModel
```

Станет:

```text
User -> FinancialModel
```

Все финансовые модели пользователя будут отображаться на одном экране `/models`, без группировки по проектам.

## Что переносим из Project в FinancialModel

`FinancialModel` становится основной рабочей сущностью пользователя.

В нее нужно перенести ответственность за:

- владельца модели: `owner User`;
- название модели: `title`;
- описание модели: `description`;
- короткий идентификатор: `shortId`;
- номер версии: `versionNumber`;
- статус: `active`, `archived`;
- дату архивации: `archivedAt`.

`TimeParams` остаются обязательным блоком при создании модели.

## Создание модели

При создании модели пользователь должен указать:

- название модели;
- описание модели;
- дату начала инвестиций;
- длительность инвестиций;
- длительность коммерческой работы;
- шаг прогнозирования.

Больше не генерируем название модели из названия проекта.

`versionNumber` пока оставляем как внутренний порядковый номер модели пользователя:

```text
nextVersionNumberForOwner(User $owner)
```

## Новые экраны

Заказчик передал две статические верстки:

- `zastavka.html` - новый первый экран приложения;
- `model_lists.html` - новый экран списка моделей.

### Главный экран

Путь:

```text
GET /
```

Назначение:

- показать приветственный экран;
- кнопка "Начать работу" ведет на `/models`.

На первом шаге главный экран можно добавить без удаления `Project`, чтобы изменение было безопасным.

### Экран моделей

Путь:

```text
GET /models
```

Назначение:

- показать все модели текущего пользователя;
- показать кнопку создания новой модели;
- открыть существующую модель;
- в будущем показывать расчетные метрики по модели.

Верстка из `model_lists.html` пока использует слова "проект", но в продуктовой логике это уже будет "модель" или "инвестиционная модель".

## Новые маршруты

Целевая web-схема:

```text
GET /
GET /models
GET /models/{financialModelShortId}/edit/{tabKey}
```

Целевая API-схема:

```text
POST   /api/models
PATCH  /api/models/{financialModelShortId}
POST   /api/models/{financialModelShortId}/archive
POST   /api/models/{financialModelShortId}/restore
DELETE /api/models/{financialModelShortId}

PATCH /api/models/{financialModelShortId}/time-params
POST  /api/models/{financialModelShortId}/preview

GET  /api/models/{financialModelShortId}/exports/excel
POST /api/models/{financialModelShortId}/exports/excel
GET  /api/models/{financialModelShortId}/exports/excel/{exportId}/download
```

Старые маршруты с `projectShortId` нужно будет убрать:

```text
/projects/{projectShortId}/...
/api/projects/{projectShortId}/...
```

## Миграция данных

Переход нужно делать аккуратно, отдельным этапом.

Ожидаемая миграция:

1. Добавить `owner_id` в `financial_models`.
2. Добавить `description` в `financial_models`.
3. Заполнить `financial_models.owner_id` из `projects.owner_id`.
4. При необходимости перенести `projects.description` в `financial_models.description` для существующих данных.
5. Убрать `financial_models.project_id`.
6. Убрать `excel_exports.project_id`.
7. Удалить таблицу `projects`.
8. Пересобрать индексы и unique constraints под `owner_id`.

Целевые constraints:

- `financial_models.short_id` остается уникальным;
- `financial_models.owner_id, version_number` должны быть уникальны вместе;
- индексы по `owner_id`, `owner_id + status`.

## Что нужно переделать в backend

### Удалить Project-слой

Под удаление попадут:

- `src/Entity/Project.php`;
- `src/Application/Project/`;
- `src/Controller/Web/Project/`;
- `src/Controller/Api/Project/`;
- `src/Domain/Project/`;
- project request DTO, mappers, response factories;
- старые Twig-блоки project workspace;
- тесты Project use cases.

### Переделать FinancialModel слой

Нужно заменить проверки вида:

```text
projectShortId + financialModelShortId + owner
```

на:

```text
financialModelShortId + owner
```

Репозиторий должен уметь:

- найти модель по `shortId` и владельцу;
- найти все модели владельца;
- получить следующий `versionNumber` для владельца;
- сохранить модель.

### Переделать зависимые use cases

Под новую схему нужно адаптировать:

- TimeParams edit/update;
- FinancialModel summary;
- CalculationResult preview;
- ExcelExport create/list/download;
- internal worker endpoints, если они возвращают project metadata;
- response factories;
- Stimulus controllers, которые сейчас передают `projectShortId`.

## Что делаем по шагам

### Шаг 15.1. Зафиксировать новый план

Цель:

- сохранить новое решение в документации;
- не потерять контекст после паузы.

Результат:

- этот документ создан;
- `docs/specification/README.md` обновлен.

Проверка:

- документ есть в списке архивного ТЗ.

### Шаг 15.2. Добавить главный экран `/`

Цель:

- перенести `zastavka.html` в Symfony/Twig;
- кнопка "Начать работу" ведет на `/models`.

Что изучаем:

- web controller;
- Twig template;
- page-specific CSS;
- аккуратный перенос статической верстки.

Файлы:

- `src/Controller/Web/Home/HomePageController.php`;
- `templates/home/index.html.twig`;
- `assets/styles/pages/home.css`;
- `assets/styles/app.css`.

Проверка:

- открыть `/`;
- кнопка ведет на `/models`;
- верстка визуально соответствует `zastavka.html`.

### Шаг 15.3. Подготовить экран `/models` на новой верстке

Перед этим шагом добавляем отдельный подэтап 15.2.1.

### Шаг 15.2.1. EasyAdmin для главной страницы

Цель:

- подключить EasyAdmin;
- сделать админ-раздел для редактирования текстов и SEO главной страницы;
- убрать жестко заданные тексты главной страницы из Twig.

Что изучаем:

- установку Symfony bundle;
- EasyAdmin Dashboard;
- EasyAdmin CRUD controller;
- singleton settings entity;
- Symfony security для `/admin`;
- как frontend-страница читает настройки из application layer.

Пакет:

```text
easycorp/easyadmin-bundle
```

Новая сущность:

```text
HomePageSettings
```

Поля:

- `id`;
- `heroTitle`;
- `heroSubtitle`;
- `ctaLabel`;
- `ctaUrl`;
- `seoTitle`;
- `seoDescription`;
- `ogTitle nullable`;
- `ogDescription nullable`;
- `createdAt`;
- `updatedAt`.

Файлы:

- `src/Entity/HomePageSettings.php`;
- `src/Repository/HomePageSettingsRepository.php`;
- `src/Application/HomePage/GetHomePageSettings/...`;
- `src/Controller/Admin/DashboardController.php`;
- `src/Controller/Admin/HomePageSettingsCrudController.php`;
- миграция;
- тесты application handler-а.

Правило:

- EasyAdmin используется только для административного CRUD;
- публичный controller `/` не должен напрямую зависеть от EasyAdmin;
- главная страница получает данные через обычный application handler/page builder.

Проверка:

- `/admin` открывает EasyAdmin dashboard;
- в `/admin` можно изменить настройки главной страницы;
- `/` отображает новые значения;
- `<title>` и meta description берутся из настроек;
- `doctrine:schema:validate` зеленый;
- `php bin/phpunit` зеленый.

### Шаг 15.3. Подготовить экран `/models` на новой верстке

Цель:

- перенести `model_lists.html` в Twig;
- пока можно временно использовать текущие данные через существующий Project-слой или заглушку;
- не удалять `Project` в этом шаге.

Что изучаем:

- разделение верстки и данных;
- карточки моделей;
- пустое состояние;
- будущую форму создания модели.

Файлы:

- `src/Controller/Web/FinancialModel/FinancialModelListPageController.php`;
- page builder для списка моделей;
- `templates/financial_model/list.html.twig`;
- `assets/styles/pages/model-list.css`;
- возможно новый Stimulus controller для создания модели.

Проверка:

- открыть `/models`;
- увидеть список моделей или пустое состояние;
- карточка ведет к странице редактирования модели.

### Шаг 15.4. Перевести backend на User -> FinancialModel

Цель:

- убрать `Project` из доменной схемы и use cases.

Что изучаем:

- безопасную миграцию Doctrine;
- изменение связей ORM;
- обновление application layer без fat controllers.

Файлы:

- `src/Entity/FinancialModel.php`;
- `src/Entity/ExcelExport.php`;
- repository interfaces и Doctrine implementations;
- FinancialModel use cases;
- TimeParams use cases;
- ExcelExport use cases;
- migrations;
- tests.

Проверка:

- `doctrine:schema:validate`;
- `php bin/phpunit`;
- ручная проверка создания модели, редактирования TimeParams, preview, export, download.

### Шаг 15.5. Удалить старый Project UI/API

Цель:

- убрать старые маршруты, шаблоны и тесты, которые больше не соответствуют продукту.

Проверка:

- `rg "Project|projectShortId|/projects"` показывает только допустимые упоминания в архивной документации или миграциях;
- старые `/projects` routes больше не используются;
- новая схема `/models` работает.

### Шаг 15.6. После этого вернуться к Первоначальным инвестициям

Цель:

- изучить новое ТЗ по блоку "Первоначальные инвестиции";
- строить его уже поверх новой схемы без Project.

## Как мы работаем дальше

Работаем в учебном режиме:

1. Я объясняю следующий небольшой шаг.
2. Я говорю, какие файлы создать или изменить.
3. Ты делаешь шаг сам.
4. Ты показываешь результат.
5. Я проверяю архитектуру, нейминг, Doctrine-связи, контроллеры, тесты и соответствие этапу.
6. Если есть небольшая или сложная проблема, я могу сам внести правку как учебный пример.
7. Без твоего подтверждения не переходим к следующему шагу.

Важно:

- не генерируем сразу всю переделку;
- сначала UI-экраны, потом удаление `Project`;
- не смешиваем миграцию архитектуры и новое ТЗ по инвестициям в один коммит;
- каждый крупный шаг фиксируем отдельным коммитом.
