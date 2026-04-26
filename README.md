# Сервис для генерации Excel финансовых моделей

Веб-приложение для создания, редактирования, расчета, визуализации и экспорта финансовых моделей проектов.
___

## Содержание
1. [Описание доменной модели](docs/architecture/domain-model.md)
2. [Описание схемы БД](docs/architecture/storage-model.md)
3. [Описание Application Use Cases](docs/architecture/use-cases.md)
4. [Описание структуры symfony проекта](docs/architecture/symfony-structure.md)
5. [Расчеты и output-каналы](docs/architecture/calculation-and-outputs.md)


## Локальный запуск через Docker

### Требования

- Docker
- Docker Compose
- Make

### Первый запуск

Основной вариант запуска:

```bash
make setup
```

Эта команда соберет PHP-контейнер, запустит сервисы, установит зависимости, создаст базу данных, применит миграции, проверит схему БД и запустит тесты.

Приложение будет доступно по адресу:

```text
http://localhost:7777
```

### Пошаговый запуск

Собрать PHP-контейнер:

```bash
make build
```

Запустить сервисы:

```bash
make up
```

Проверить контейнеры:

```bash
make ps
```

Установить PHP-зависимости внутри контейнера:

```bash
make composer-install
```

Создать базу данных, если она еще не создана:

```bash
make db-create
```

Применить миграции:

```bash
make migrate
```

Проверить соответствие Doctrine mapping и базы данных:

```bash
make schema-validate
```

Запустить тесты:

```bash
make test
```

### Полезные команды

Остановить контейнеры:

```bash
make down
```

Посмотреть логи:

```bash
make logs
```

Посмотреть логи PHP:

```bash
make logs-php
```

Посмотреть логи Nginx:

```bash
make logs-nginx
```

Зайти внутрь PHP-контейнера:

```bash
make shell
```

### Сервисы

- `php-fpm` — PHP 8.4 runtime для Symfony.
- `nginx` — веб-сервер, доступен на `http://localhost:7777`.
- `database` — PostgreSQL 16, внутри Docker доступен как `database:5432`.
- `mailer` — Mailpit для локальной почты.

### Важное правило по БД

Не использовать:

```bash
doctrine:schema:update --force
```

Схему базы меняем только через migrations:

```bash
docker compose exec php-fpm php bin/console make:migration
make migrate
```
