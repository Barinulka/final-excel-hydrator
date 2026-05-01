# 11. Go worker для Excel export

## Цель

Создать первый Go-сервис, который умеет общаться с Symfony internal API и закрывать lifecycle Excel export-задачи.

На первом шаге worker использовал mock generator. Дальше generator был расширен и начал создавать настоящий `.xlsx` из `CalculationResultPayload`.

## Почему отдельный Go-сервис

Excel не должен генерироваться в PHP.

Symfony отвечает за:

- бизнес-логику финансовой модели;
- расчет `CalculationResult`;
- создание `ExcelExport`;
- хранение snapshot-а расчета;
- status lifecycle export-задачи.

Go worker отвечает за техническую часть export-а:

- получить pending-задачу;
- взять ее в обработку;
- сгенерировать файл;
- вернуть Symfony результат.

## Структура

```text
go-services/
  excel-worker/
    go.mod
    README.md
    cmd/
      excel-worker/
        main.go
    internal/
      config/
        config.go
        config_test.go
      excel/
        generator.go
        generator_test.go
      symfony/
        client.go
        client_test.go
        dto.go
```

## Принятые решения

`cmd/excel-worker/main.go` является точкой входа приложения.

Пакеты внутри `internal/` считаются внутренней реализацией worker-а и не предназначены для импорта другими Go-модулями.

`internal/config` читает конфигурацию из env:

- `SYMFONY_INTERNAL_BASE_URL`;
- `STORAGE_ROOT_DIR`;
- `EXCEL_EXPORTS_DIR`.

`internal/symfony` содержит HTTP client и DTO для Symfony internal API.

`internal/excel` содержит Excel generator. Он создает `.xlsx` и возвращает путь:

```text
excel-exports/excel-export-{id}.xlsx
```

Это относительный путь. Физически файл сохраняется в `STORAGE_ROOT_DIR/EXCEL_EXPORTS_DIR`.

## Worker flow

Текущий worker выполняет один проход:

1. Загружает конфиг.
2. Получает pending export-задачи:

```text
GET /internal/excel-exports/pending?limit=10
```

3. Если задач нет, завершает работу.
4. Берет первую задачу.
5. Переводит ее в `processing`:

```text
POST /internal/excel-exports/{exportId}/processing
```

6. Вызывает Excel generator.
7. Если generator создал файл и вернул путь, переводит задачу в `completed`:

```text
POST /internal/excel-exports/{exportId}/completed
```

8. Если generator вернул ошибку, переводит задачу в `failed`:

```text
POST /internal/excel-exports/{exportId}/failed
```

## Защита HTTP client-а

Symfony client проверяет:

- redirect;
- HTTP status code;
- `Content-Type`;
- корректность JSON response.

Это важно, потому что на этапе разработки уже была поймана ошибка, когда Symfony security возвращал redirect на `/login`, а Go пытался декодировать HTML как JSON.

## Тесты

Покрыты:

- загрузка конфига;
- обязательный `STORAGE_ROOT_DIR`;
- default `EXCEL_EXPORTS_DIR`;
- Excel generator;
- Symfony HTTP client через `httptest.Server`;
- redirect на `/login`;
- HTML вместо JSON;
- `processing`, `completed`, `failed` request payload.

## Проверка

Локально из папки `go-services/excel-worker`:

```bash
go fmt ./...
go test ./...
SYMFONY_INTERNAL_BASE_URL=http://127.0.0.1:7777 STORAGE_ROOT_DIR=../../var/storage EXCEL_EXPORTS_DIR=excel-exports go run ./cmd/excel-worker
```

Ожидаемый результат:

- Go-код форматируется;
- тесты зеленые;
- worker получает pending-задачи через Symfony;
- одна pending-задача переходит в `processing`, затем в `completed`.

## Что сознательно отложено

- download endpoint в Symfony;
- service token/header для internal API;
- постоянный worker loop;
- retries;
- конкурентная обработка несколькими worker-ами.
