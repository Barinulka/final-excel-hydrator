# Model Workspace Frontend Strategy

## Цель

Документ фиксирует актуальную frontend-стратегию рабочей области после удаления `Project`.

Пользователь работает не с проектами, а напрямую со списком финансовых моделей.

```text
User -> FinancialModel
```

## Основные экраны

```text
GET /
GET /models
GET /models/{financialModelShortId}/edit/{tabKey}
```

`/` — главный экран продукта.

`/models` — рабочая зона пользователя со списком его финансовых моделей.

`/models/{financialModelShortId}/edit/{tabKey}` — редактор конкретной финансовой модели.

## Экран `/models`

Назначение:

* показать все активные и архивные модели текущего пользователя;
* открыть модальное окно создания модели;
* перейти к редактированию модели;
* показать базовую информацию: название, описание, версию, статус, даты.

Источник данных:

* backend собирает view model;
* Twig рендерит HTML;
* Stimulus отвечает только за локальную интерактивность формы и модального окна.

Frontend не решает, какие модели доступны пользователю. Доступ всегда проверяется на backend по `owner`.

## Создание модели

Создание модели происходит без `Project`.

Пользователь вводит:

* название модели;
* описание модели;
* дату начала инвестиций;
* длительность инвестиций;
* длительность коммерческой работы;
* шаг прогнозирования.

Backend use case создает:

* `FinancialModel`;
* обязательный `TimeParams`.

После успешного создания frontend переводит пользователя на:

```text
/models/{financialModelShortId}/edit/dashboard
```

## Редактор модели

Редактор модели состоит из shell-страницы и вкладок.

Основной маршрут:

```text
GET /models/{financialModelShortId}/edit/{tabKey}
```

Доступ проверяется по:

```text
financialModelShortId + current user
```

Старый `projectShortId` в URL больше не используется.

## API

Актуальные API-маршруты не содержат `projectShortId`.

```text
POST   /api/models
PATCH  /api/models/{financialModelShortId}/title
PATCH  /api/models/{financialModelShortId}/details
PATCH  /api/models/{financialModelShortId}/archive
PATCH  /api/models/{financialModelShortId}/restore
DELETE /api/models/{financialModelShortId}

PUT   /api/models/{financialModelShortId}/time-params
POST  /api/models/{financialModelShortId}/preview

GET  /api/models/{financialModelShortId}/exports/excel
POST /api/models/{financialModelShortId}/exports/excel
GET  /api/models/{financialModelShortId}/exports/excel/{exportId}/download
```

## Ответственность слоев

Controller:

* принимает request;
* получает текущего пользователя;
* вызывает handler/page builder;
* возвращает HTML или JSON.

Application handler:

* выполняет use case;
* проверяет доступ через `owner`;
* работает через repository interfaces.

Twig:

* отображает готовую view model;
* не содержит business logic;
* не ходит в Doctrine.

Stimulus:

* открывает/закрывает модальные окна;
* собирает payload формы;
* вызывает JSON API;
* показывает ошибки и локальные UI-состояния.

Stimulus не должен считать финансовую модель и не должен быть источником прав доступа.

## Что важно сохранить

* `Project` не возвращаем без отдельного бизнес-решения.
* Если позже появится командная работа, вероятнее всего нужен новый уровень `Workspace`, а не возврат старого `Project`.
* Все новые блоки модели строим поверх `FinancialModel`, `owner` и `financialModelShortId`.
