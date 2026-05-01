# Архивное ТЗ проекта

## Назначение

Эта папка фиксирует понятное техническое задание по уже принятым и реализованным этапам.

Документы нужны, чтобы через несколько недель было понятно:

- какую задачу решал этап;
- какие решения были приняты;
- что уже реализовано;
- что сознательно отложено;
- как проверять результат.

Это не финальное бизнес-ТЗ всего продукта. Это рабочий архив решений по текущей ветке разработки.

## Главные правила архитектуры

- Приложение строится как модульный монолит на Symfony.
- От сложного академического DDD уходим. Используем понятную слоистую структуру: Controller -> Application Handler -> Entity/Domain Service -> Repository/Database.
- Controller не содержит business logic.
- Application handler управляет use case.
- Entity и небольшие domain services хранят инварианты и расчетные правила.
- API возвращает JSON через отдельные response factory.
- Twig отвечает за HTML-страницы.
- Stimulus отвечает за небольшую интерактивность и обращения к API.
- Excel не генерируется в PHP. Symfony только готовит данные и задачи. Генерация Excel будет вынесена в Go worker/service.

## Документы

1. [Окружение и Docker](01-environment.md)
2. [Проекты и финансовые модели](02-projects-and-financial-models.md)
3. [Редактор модели и вкладки](03-model-editor-tabs.md)
4. [Временные параметры](04-time-params.md)
5. [CalculationResult и preview расчетов](05-calculation-result-preview.md)
6. [Excel export и границы Go worker](06-excel-export-boundary.md)
7. [ExcelExport task в Symfony](07-excel-export-task.md)
8. [Список Excel export-задач](08-excel-export-list.md)
9. [Snapshot CalculationResult для Excel export](09-excel-export-calculation-snapshot.md)
10. [Worker bridge и internal API для Excel export](10-excel-export-worker-bridge.md)
11. [Go worker для Excel export](11-go-excel-worker.md)
12. [Docker для Go worker](12-go-worker-docker.md)
13. [Генерация XLSX в Go](13-go-xlsx-generation.md)
14. [Локальный storage для Excel export](14-excel-export-local-storage.md)

## Как обновлять архив

После каждого принятого этапа добавляем или обновляем документ в этой папке:

- цель этапа;
- принятое решение;
- реализованные файлы/слои;
- API или UI-контракт;
- критерии проверки;
- что отложено.
