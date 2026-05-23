# Project Workspace Frontend Strategy

## Статус

Документ устарел после отказа от сущности `Project`.

Актуальная рабочая область теперь строится вокруг списка финансовых моделей:

```text
User -> FinancialModel
```

Актуальная стратегия описана в `docs/architecture/model-workspace.md`.

Этот файл оставлен только как исторический контекст: он показывает, почему раньше планировался `/projects` workspace и почему затем от него отказались.

## Старое решение

## Основная идея
Project workspace — это рабочая область, где пользователь видит список своих проектов и содержимое выбранного проекта.

Страница не должна быть просто списком всех проектов. Смысл экрана:
* слева — навигация по проектам пользователя;
* справа — выбранный Project и его FinancialModel;
* создание FinancialModel происходит из контекста выбранного Project;
* переход к редактированию FinancialModel происходит из карточки модели.

## Routes

### `/projects`
Вход в рабочую область проектов.

Поведение:
* если проектов нет — показывается пустое состояние;
* если проекты есть — пользователь должен попасть в workspace с выбранным проектом.

Предпочтительное поведение для первого рабочего варианта: redirect на первый доступный активный Project.

### `/projects/{projectShortId}`
Рабочая область с выбранным Project.

Этот route является основным route для просмотра Project и списка его FinancialModel.

URL должен отражать выбранный Project, чтобы:
* страницу можно было обновить;
* ссылку можно было передать;
* back/forward в браузере работали предсказуемо;
* frontend не становился источником выбранного состояния.

## Layout
Workspace состоит из двух областей:

```text
Project Workspace
├── Sidebar: список Project
└── Content: выбранный Project и его FinancialModel
```

Sidebar:
* показывает проекты текущего пользователя;
* подсвечивает активный Project;
* архивные Project показывает ниже активных и визуально приглушает;
* не содержит бизнес-логики.

Content:
* показывает summary выбранного Project;
* показывает кнопку создания FinancialModel;
* показывает список FinancialModel выбранного Project;
* активные FinancialModel идут выше архивных;
* архивные FinancialModel визуально приглушены;
* действия архивных FinancialModel ограничены состоянием, пришедшим из backend view model.

Правая область не должна дублировать список всех проектов. Список проектов уже находится в sidebar.

## Frontend Strategy
Используем server-rendered UI на Twig с progressive enhancement через Turbo Frames и Stimulus.

Это означает:
* backend собирает view model;
* Twig рендерит HTML;
* Turbo обновляет часть страницы без полной перезагрузки;
* Stimulus отвечает за локальный интерактив;
* frontend не хранит бизнес-состояние как source of truth.

Отдельное SPA-приложение на первом этапе не вводим.

## Turbo Frame Strategy
Project workspace рендерится внутри одного Turbo Frame:

```html
<turbo-frame id="project_workspace" data-turbo-action="advance">
    ...
</turbo-frame>
```

Внутри frame находятся:
* project sidebar;
* project content.

При выборе Project ссылка ведет на обычный route:

```text
/projects/{projectShortId}
```

Ссылка также указывает Turbo target:

```html
data-turbo-frame="project_workspace"
data-turbo-action="advance"
```

Turbo загружает HTML с backend, заменяет `project_workspace`, а URL обновляется до выбранного Project.

## Почему обновляем весь workspace frame
Обновляем не только правую часть, а весь workspace frame.

Причина:
* backend заново собирает полное состояние страницы;
* Twig сам правильно выставляет active state в sidebar;
* не нужно вручную синхронизировать active class через Stimulus;
* back/forward и прямой заход по URL остаются проще;
* frontend не становится источником выбранного Project.

Да, в ответе будет больше HTML, чем при обновлении только правой области. Это допустимый tradeoff: sidebar небольшой, а упрощение состояния важнее.

## Stimulus Responsibilities
Stimulus используется только для локального интерактива:
* открыть/закрыть modal создания FinancialModel;
* собрать JSON payload формы;
* отправить JSON request;
* показать field errors;
* показать toast;
* раскрыть/свернуть UI-блоки;
* будущий formula builder.

Stimulus не должен:
* рассчитывать финансовую модель;
* решать, какие проекты или модели доступны пользователю;
* быть source of truth для active Project;
* собирать summary из DOM;
* держать бизнес-состояние финансовых вкладок.

## Create FinancialModel Flow
Создание FinancialModel происходит из выбранного Project.

Flow:
* пользователь выбирает Project;
* нажимает "Создать модель";
* открывается modal;
* пользователь заполняет обязательные TimeParams;
* frontend отправляет JSON request;
* backend валидирует request DTO;
* application use case создает FinancialModel и обязательный TimeParams;
* backend возвращает target URL;
* frontend перенаправляет пользователя на страницу редактирования FinancialModel.

Создание FinancialModel не должно быть отдельной полноценной страницей, если пользователь вводит только стартовые TimeParams.

## Page Builder / View Model
Для workspace нужен Presentation page builder.

Рекомендуемая структура:

```text
src/Presentation/Web/Page/ProjectWorkspace/
  ProjectWorkspacePage.php
  ProjectWorkspacePageBuilder.php
```

Page builder собирает:
* список Project для sidebar;
* выбранный Project;
* `selectedProjectShortId`;
* список FinancialModel выбранного Project;
* empty state flags;
* route/action metadata, необходимую Twig;
* read-only/archived flags для отображения.

Page builder может использовать application query handlers:
* `GetProjectListHandler`;
* `GetProjectPageHandler`.

Page builder не должен:
* обращаться к Doctrine напрямую;
* выполнять бизнес-операции;
* применять request data к entity;
* считать финансовую модель;
* знать детали всех финансовых вкладок.

## Twig Responsibilities
Twig получает подготовленную view model.

Twig может:
* отрисовать sidebar;
* отрисовать active state;
* отрисовать project summary;
* отрисовать cards FinancialModel;
* отрисовать empty state;
* поставить route/action URL из view model или route helper.

Twig не должен:
* загружать данные;
* фильтровать модели по владельцу;
* решать business permissions;
* выполнять расчеты;
* собирать данные для графиков;
* содержать hardcoded список финансовых вкладок.

## API-first Financial Tabs
Project workspace использует Twig/Turbo для навигации и HTML shell.

Финансовые вкладки внутри FinancialModel сохраняются отдельно через JSON API:
* TimeParams;
* Investments;
* будущие блоки.

Это два разных уровня:
* workspace navigation — HTML/Turbo;
* financial tab save — JSON API-first.

## Future Enhancements
Позже можно добавить:
* Turbo Streams для точечного обновления списка моделей после создания/архивации;
* lazy loading отдельных тяжелых блоков;
* skeleton/loading states;
* более сложный Stimulus controller для formula builder;
* отдельный frontend framework, если формульный редактор или интерактивные таблицы реально перерастут возможности Twig/Turbo/Stimulus.

Отдельный frontend framework не вводим заранее. Решение о нем принимается только при реальной сложности, которую текущий подход не покрывает.

## Что Не Делаем
* Не делаем правую область вторым списком всех Project.
* Не делаем frontend source of truth для выбранного Project.
* Не строим SPA на первом этапе.
* Не переносим бизнес-логику в Stimulus.
* Не собираем project/model state из DOM.
* Не завязываем layout на одну конкретную вкладку FinancialModel.
* Не переносим старый prototype UI механически.

## Resolved Decisions
* Project workspace строится как Twig + Turbo Frames + Stimulus.
* `/projects` является входом в workspace.
* `/projects/{projectShortId}` является workspace с выбранным Project.
* Sidebar и content находятся внутри одного Turbo Frame `project_workspace`.
* При выборе Project обновляется весь workspace frame.
* URL должен отражать выбранный Project.
* Stimulus используется для локального интерактива, а не для бизнес-состояния.
* Financial tabs остаются JSON API-first.
