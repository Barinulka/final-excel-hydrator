# 01. Окружение и Docker

## Цель

Поднять локальное окружение, в котором проект можно запускать и проверять одинаково на любой машине разработчика.

## Реализовано

- Docker Compose окружение.
- PHP-FPM контейнер для Symfony.
- Nginx контейнер для HTTP.
- PostgreSQL 16 контейнер.
- Mailpit контейнер для локальной почты.
- Makefile с русским `help`.
- README с инструкцией первого запуска.

## Основные команды

Первый запуск:

```bash
make setup
```

Запуск сервисов:

```bash
make up
```

Проверка контейнеров:

```bash
make ps
```

Применение миграций:

```bash
make migrate
```

Проверка схемы Doctrine:

```bash
make schema-validate
```

Запуск тестов:

```bash
make test
```

## Принятые правила

- Схему базы меняем только через Doctrine Migrations.
- `doctrine:schema:update --force` не используем.
- Перед приемкой этапа проверяем миграции, схему и тесты.

## Критерии готовности

- `docker compose ps` показывает запущенные сервисы.
- PostgreSQL healthy.
- `doctrine:schema:validate` показывает, что mapping и база синхронизированы.
- PHPUnit проходит.
- Приложение доступно на `http://localhost:7777`.

