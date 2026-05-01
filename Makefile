DC=docker compose
PHP=$(DC) exec php-fpm
GO_WORKER_DIR=go-services/excel-worker

.PHONY: help build up down restart ps logs logs-php logs-nginx shell composer-install db-create migrate schema-validate test go-fmt go-test go-worker-build go-worker-run setup

help:
	@echo "Доступные команды:"
	@echo "  make setup            Собрать проект, запустить контейнеры, установить зависимости, создать БД, применить миграции, проверить схему и тесты"
	@echo "  make build            Собрать PHP-контейнер"
	@echo "  make up               Запустить контейнеры"
	@echo "  make down             Остановить контейнеры"
	@echo "  make restart          Перезапустить контейнеры"
	@echo "  make ps               Показать статус контейнеров"
	@echo "  make logs             Смотреть все логи"
	@echo "  make logs-php         Смотреть логи PHP-FPM"
	@echo "  make logs-nginx       Смотреть логи Nginx"
	@echo "  make shell            Открыть shell внутри PHP-контейнера"
	@echo "  make composer-install Установить PHP-зависимости"
	@echo "  make db-create        Создать базу данных, если ее еще нет"
	@echo "  make migrate          Применить Doctrine migrations"
	@echo "  make schema-validate  Проверить Doctrine mapping и схему базы данных"
	@echo "  make test             Запустить PHPUnit"
	@echo "  make go-fmt           Отформатировать Go worker"
	@echo "  make go-test          Запустить тесты Go worker"
	@echo "  make go-worker-build  Собрать Docker image Go worker"
	@echo "  make go-worker-run    Запустить один проход Go worker в Docker"

build:
	$(DC) build php-fpm

up:
	$(DC) up -d

down:
	$(DC) down

restart: down up

ps:
	$(DC) ps

logs:
	$(DC) logs -f

logs-php:
	$(DC) logs -f php-fpm

logs-nginx:
	$(DC) logs -f nginx

shell:
	$(PHP) sh

composer-install:
	$(PHP) composer install

db-create:
	$(PHP) php bin/console doctrine:database:create --if-not-exists

migrate:
	$(PHP) php bin/console doctrine:migrations:migrate --no-interaction

schema-validate:
	$(PHP) php bin/console doctrine:schema:validate

test:
	$(PHP) php bin/phpunit

go-fmt:
	cd $(GO_WORKER_DIR) && go fmt ./...

go-test:
	cd $(GO_WORKER_DIR) && go test ./...

go-worker-build:
	$(DC) build excel-worker

go-worker-run:
	$(DC) run --rm excel-worker

setup: build up composer-install db-create migrate schema-validate test
