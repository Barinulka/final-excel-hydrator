# 12. Docker для Go worker

## Цель

Запускать Go worker как отдельный Docker service в общей сети с Symfony.

После этого worker может обращаться к Symfony не через порт хоста, а через имя compose service:

```text
http://nginx
```

## Что реализовано

Добавлены файлы:

```text
go-services/excel-worker/Dockerfile
go-services/excel-worker/.dockerignore
```

Обновлены:

```text
compose.yaml
Makefile
go-services/excel-worker/README.md
```

## Dockerfile

Dockerfile использует несколько stages:

- `base` - общий слой с Go module;
- `test` - слой для запуска `go test ./...`;
- `builder` - сборка бинарника;
- final image на `scratch` - минимальный runtime-образ только с бинарником worker-а.

Сборка бинарника:

```text
CGO_ENABLED=0 GOOS=linux go build -o /bin/excel-worker ./cmd/excel-worker
```

Почему `scratch`: текущему worker-у не нужен shell, package manager или дополнительные runtime-зависимости.

## Compose service

В `compose.yaml` добавлен service:

```yaml
excel-worker:
  build:
    context: ./go-services/excel-worker
  container_name: 'excel-worker'
  depends_on:
    - nginx
  environment:
    SYMFONY_INTERNAL_BASE_URL: "http://nginx"
    STORAGE_ROOT_DIR: "/app/var/storage"
    EXCEL_EXPORTS_DIR: "excel-exports"
  volumes:
    - ./var/storage:/app/var/storage
  profiles:
    - worker
```

`profiles: ["worker"]` нужен специально.

Worker сейчас выполняет один проход и меняет статус pending export-задачи. Поэтому обычная команда:

```bash
docker compose up -d
```

не должна случайно запускать worker.

Worker запускается явно:

```bash
docker compose run --rm excel-worker
```

## Makefile

Добавлены команды:

```bash
make go-fmt
make go-test
make go-worker-build
make go-worker-run
```

Назначение:

- `go-fmt` - отформатировать Go-код;
- `go-test` - запустить Go-тесты локально;
- `go-worker-build` - собрать Docker image worker-а;
- `go-worker-run` - выполнить один проход worker-а внутри Docker.

## Проверка

```bash
make go-fmt
make go-test
make go-worker-build
make go-worker-run
```

Ожидаемый результат:

- Go-код форматируется;
- Go-тесты зеленые;
- Docker image собирается;
- worker внутри Docker обращается к Symfony по `http://nginx`;
- pending export-задача переходит в `processing`, затем в `completed`.

## Что сознательно отложено

- постоянный запуск worker-а как daemon;
- scheduler/cron;
- healthcheck worker-а;
- CI pipeline для Go-тестов;
- постоянный worker loop.
