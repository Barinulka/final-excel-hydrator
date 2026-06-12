# 16. Первоначальные инвестиции: категории расходов

## Статус

Реализованный этап.

Полного бизнес-ТЗ пока нет. Документ фиксирует принятое и реализованное решение по первому маленькому шагу вкладки "Первоначальные инвестиции".

## Контекст

Вкладка `initial_investments` зарегистрирована в редакторе финансовой модели и отображает верхнеуровневые категории расходов.

В коде есть:

* `InvestmentBlock` — блок инвестиций внутри `FinancialModel`;
* `InvestmentItem` — будущая конкретная строка расхода с суммой, НДС, амортизацией и налоговыми признаками.

На этом этапе не добавляем конкретные расходы внутрь категории. Сделаны только верхнеуровневые категории расходов.

## Продуктовое поведение

На вкладке "Первоначальные инвестиции" пользователь должен видеть кнопку:

```text
+ Добавить категорию
```

При нажатии открывается модальное окно.

В модальном окне пользователь выбирает название категории из списка:

* Оборудование;
* Мебель и оргтехника;
* Ремонт помещения;
* ПО, сайт, лицензии;
* Транспорт;
* НМА и патенты;
* Прочее;
* Свое название.

Если выбрано "Свое название", пользователь вводит название вручную.

Также пользователь указывает сопутствующие расходы для этой категории.

После сохранения на вкладке появляется строка/карточка с данными категории.

Сохраненные категории загружаются из БД при открытии вкладки и после обновления страницы.

Пользователь может удалить верхнеуровневую категорию через круглую кнопку с иконкой корзины.

Рядом с кнопкой удаления отображается круглая кнопка с шевроном вниз. Сейчас это визуальная заготовка под будущее раскрытие категории.

В будущем каждую категорию можно будет раскрыть и внутри нее добавить конкретные расходы через кнопку:

```text
+ Добавить расходы
```

Этот future flow пока не реализуем.

## Архитектурное решение

Не используем `InvestmentItem` для верхнеуровневой категории.

Причина:

* `InvestmentItem` уже моделирует конкретную расходную строку;
* у `InvestmentItem` есть сумма, НДС, амортизация, налоговые признаки;
* верхнеуровневая категория — это контейнер/группа для будущих расходов;
* если смешать эти уровни сейчас, позже будет сложнее добавить реальные строки расходов внутри категории.

Вводим отдельную сущность верхнего уровня, рабочее имя:

```text
InvestmentExpenseCategory
```

Связь:

```text
FinancialModel
  -> InvestmentBlock
      -> InvestmentExpenseCategory
          -> future InvestmentItem[]
```

## Черновая структура сущности

```text
InvestmentExpenseCategory
```

Поля:

* `id`;
* `investmentBlock`;
* `type nullable enum/string`;
* `customTitle nullable string`;
* `relatedExpenses nullable text`;
* `createdAt`;
* `updatedAt`.

Правила:

* категория не может существовать без `InvestmentBlock`;
* если выбрана предопределенная категория, `type` обязателен;
* если выбрано свое название, `customTitle` обязателен;
* итоговое отображаемое название берется из `customTitle` или label выбранного `type`;
* `relatedExpenses` пока хранится как свободный текст;
* на фронте `relatedExpenses` отображается как сумма в рублях, если введены цифры;
* конкретные строки расходов внутри категории на этом этапе не вводим.

## Enum категорий

Рабочее имя:

```text
InvestmentExpenseCategoryType
```

Значения:

* `equipment` — Оборудование;
* `furnitureAndOfficeEquipment` — Мебель и оргтехника;
* `renovation` — Ремонт помещения;
* `softwareWebsiteLicenses` — ПО, сайт, лицензии;
* `transport` — Транспорт;
* `intangibleAssetsAndPatents` — НМА и патенты;
* `other` — Прочее.

"Свое название" не является enum-значением. Это UI-режим, при котором сохраняется `customTitle`.

## Use case

Реализованы use cases:

```text
AddInvestmentExpenseCategory
ListInvestmentExpenseCategories
DeleteInvestmentExpenseCategory
```

Flow добавления:

1. Controller принимает JSON request.
2. Controller достает текущего пользователя.
3. Request mapper собирает command.
4. Handler ищет `FinancialModel` по `financialModelShortId + owner`.
5. Handler получает существующий `InvestmentBlock` или создает новый.
6. Handler создает `InvestmentExpenseCategory`.
7. Handler сохраняет изменения.
8. API возвращает JSON с созданной категорией.

Контроллер остается тонким.

Flow списка:

1. Controller получает текущего пользователя.
2. Handler ищет `FinancialModel` по `financialModelShortId + owner`.
3. Handler получает `InvestmentBlock`.
4. Если блока нет, возвращается пустой список.
5. Если блок есть, категории преобразуются в DTO.
6. API возвращает JSON со списком категорий.

Flow удаления:

1. Controller получает текущего пользователя.
2. Handler ищет `FinancialModel` по `financialModelShortId + owner`.
3. Handler запрещает изменение архивной модели.
4. Handler получает `InvestmentBlock`.
5. Handler ищет категорию внутри блока по `categoryId`.
6. Handler удаляет категорию из коллекции `InvestmentBlock`.
7. Doctrine удаляет строку через `orphanRemoval`.
8. API возвращает признак успешного удаления.

## API

Endpoint добавления:

```text
POST /api/models/{financialModelShortId}/initial-investments/categories
```

Пример request:

```json
{
  "type": "equipment",
  "customTitle": null,
  "relatedExpenses": "Доставка, монтаж, пусконаладка"
}
```

Для своего названия:

```json
{
  "type": null,
  "customTitle": "Кухонная линия",
  "relatedExpenses": "Проектирование, доставка, монтаж"
}
```

Пример response:

```json
{
  "financialModelShortId": "ab23456789",
  "title": "Оборудование",
  "type": "equipment",
  "customTitle": null,
  "relatedExpenses": "Доставка, монтаж, пусконаладка"
}
```

Endpoint списка:

```text
GET /api/models/{financialModelShortId}/initial-investments/categories
```

Пример response:

```json
{
  "items": [
    {
      "id": 12,
      "type": "equipment",
      "title": "Оборудование",
      "relatedExpenses": "1000000"
    }
  ]
}
```

Endpoint удаления:

```text
DELETE /api/models/{financialModelShortId}/initial-investments/categories/{categoryId}
```

Пример response:

```json
{
  "data": {
    "id": 12,
    "isDeleted": true
  }
}
```

## UI

Реализованные UI-файлы:

* `templates/financial_model/tabs/_initial_investments.html.twig`;
* `assets/controllers/investment_expense_category_controller.js`;
* `assets/styles/pages/financial-model.css`.

UI должен соответствовать текущей деловой минималистичной верстке редактора модели.

На вкладке реализованы:

* кнопка `+ Добавить категорию`;
* модальное окно добавления категории;
* список сохраненных категорий;
* empty-state, если категорий нет;
* карточка категории с названием и расходом;
* форматирование расхода как `1 000 000 ₽`;
* кнопка удаления категории с иконкой корзины;
* шеврон как будущий affordance раскрытия категории.

## Что не делаем на этом этапе

* не добавляем конкретные расходы внутри категории;
* не моделируем `relatedExpenses` как money-поле в БД;
* не строим график финансирования;
* не считаем CAPEX;
* не протягиваем категории в `CalculationResult`;
* не меняем Excel export;
* не проектируем налоги и амортизацию.
* не реализуем раскрытие категории;
* не реализуем редактирование категории;
* не добавляем подтверждение удаления.

## Проверка

Backend:

```bash
docker compose exec php-fpm php bin/console doctrine:schema:validate
docker compose exec php-fpm php bin/phpunit
docker compose exec php-fpm php bin/console lint:container
```

Ручная проверка:

* открыть `/models/{financialModelShortId}/edit/initial_investments`;
* нажать `+ Добавить категорию`;
* выбрать категорию из списка;
* заполнить сопутствующие расходы числом, например `1000000`;
* сохранить;
* увидеть новую строку/карточку на вкладке;
* проверить формат `1 000 000 ₽`;
* обновить страницу и убедиться, что категория загрузилась из БД;
* удалить категорию через кнопку с корзиной;
* обновить страницу и убедиться, что категория не вернулась;
* проверить вариант со своим названием.

Реализованные тесты:

* `AddInvestmentExpenseCategoryHandlerTest`;
* `ListInvestmentExpenseCategoriesHandlerTest`;
* `DeleteInvestmentExpenseCategoryHandlerTest`;

## Следующий этап

После принятия верхнеуровневых категорий можно будет проектировать:

* раскрытие категории;
* добавление конкретных расходов внутри категории;
* суммы;
* НДС;
* график финансирования по периодам;
* вклад в `CalculationResult`;
* Excel export.
