# 02. Проекты и финансовые модели

## Цель

Заложить базовую структуру приложения:

```text
User -> Project -> FinancialModel
```

Пока не вводим Workspace, ProjectMember и Client, но не блокируем их добавление в будущем.

## Project

Project - рабочая область пользователя.

Текущие важные поля:

- `id`;
- `shortId`;
- `owner`;
- `title`;
- `description`;
- `status`;
- `createdAt`;
- `updatedAt`;
- `archivedAt`.

## FinancialModel

FinancialModel - конкретная модель или сценарий внутри проекта.

Текущие важные поля:

- `id`;
- `shortId`;
- `project`;
- `title`;
- `versionNumber`;
- `status`;
- `timeParams`;
- `createdAt`;
- `updatedAt`;
- `archivedAt`.

## Принятые решения

- Оставляем `shortId`, потому что он удобен для URL и не раскрывает внутренний integer id.
- Оставляем `title`, а не переименовываем в `name`.
- Оставляем `versionNumber`, потому что модель создается как версия внутри проекта.
- `scenarioType` пока не вводим. Нужно сначала понять реальные сценарии использования.
- Статус `draft` пока не нужен.
- `archivedAt` нужен для проектов и моделей.
- Архивную модель можно восстановить.
- Удаление архивной модели является окончательным удалением.

## Use cases

Реализованные use cases проекта:

- создание проекта;
- редактирование данных проекта;
- архивирование проекта.

Реализованные use cases финансовой модели:

- создание модели;
- переименование модели;
- архивирование модели;
- восстановление модели;
- удаление модели.

## Создание финансовой модели

При создании модели:

- handler ищет проект текущего пользователя;
- запрещает создание модели в архивном проекте;
- генерирует `shortId`;
- рассчитывает следующий `versionNumber`;
- автоматически формирует `title` из названия проекта и номера версии;
- создает обязательный блок `TimeParams`;
- возвращает shortId проекта, shortId модели, title и versionNumber.

Решение: модель не может существовать без временных параметров, потому что остальные расчетные блоки опираются на расчетный горизонт.

## API endpoints

Основные API endpoints:

```text
POST   /api/projects
PATCH  /api/projects/{projectShortId}
PATCH  /api/projects/{projectShortId}/archive

POST   /api/projects/{projectShortId}/models
PATCH  /api/projects/{projectShortId}/models/{financialModelShortId}/title
PATCH  /api/projects/{projectShortId}/models/{financialModelShortId}/archive
PATCH  /api/projects/{projectShortId}/models/{financialModelShortId}/restore
DELETE /api/projects/{projectShortId}/models/{financialModelShortId}
```

## Критерии готовности

- Controller остается тонким.
- Поиск всегда учитывает текущего пользователя.
- Нельзя работать с чужим проектом или моделью.
- Нельзя создать модель в архивном проекте.
- Архивирование выставляет `archivedAt`.
- Восстановление модели снимает архивный статус.
- Use cases покрыты тестами.

