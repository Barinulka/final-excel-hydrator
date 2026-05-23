# Domain Model

## Актуальная схема

Сущность `Project` удалена из продукта.

Было:

```text
User -> Project -> FinancialModel
```

Стало:

```text
User -> FinancialModel
```

Причина изменения: заказчик отказался от отдельного уровня проектов. Пользователь работает со списком финансовых моделей напрямую на экране `/models`.

`FinancialModel` теперь является основной рабочей сущностью пользователя: она хранит название, описание, владельца, статус, версию и все расчетные блоки.

## Project

`Project` больше не является частью актуальной доменной модели.

Удалены:

* `Project` entity;
* `ProjectRepository`;
* project application use cases;
* project web/API controllers;
* project Twig workspace;
* связи `FinancialModel.project`;
* связи `ExcelExport.project`;
* таблица `projects`.

Исторический контекст перехода зафиксирован в `docs/specification/15-models-without-projects-transition.md`.

___

## FinancialModel

### Назначение
FinancialModel — основная рабочая сущность пользователя.

Одна FinancialModel описывает конкретную финансовую модель бизнеса: временные параметры, инвестиции, продажи, затраты, расчеты, Excel export и будущий AI-анализ.

FinancialModel больше не находится внутри Project. Доступ к модели проверяется по связке `financialModelShortId + owner`.

### Поля
* id — внутренняя identity финансовой модели
* ownerId — identity пользователя, которому принадлежит модель
* shortId — публичный стабильный идентификатор финансовой модели
* title — название финансовой модели
* description — короткое описание модели, optional
* versionNumber — порядковый номер модели пользователя
* sourceModelId — optional, ссылка на исходную модель, если модель была создана копированием
* status — статус финансовой модели
* amountDisplayFormat — формат отображения сумм для этой финансовой модели
* createdAt — audit timestamp
* updatedAt — audit timestamp
* archivedAt — дата архивации, optional

### Зависимости / связи
* FinancialModel принадлежит User
* FinancialModel имеет один обязательный TimeParams
* FinancialModel содержит доменные блоки, определенные кодом приложения
* FinancialModel не содержит произвольные пользовательские вкладки
* FinancialModel не хранит данные всех вкладок в одном общем JSON

### Инварианты
* FinancialModel не может существовать без owner
* FinancialModel не может быть создана без TimeParams
* shortId должен генерироваться автоматически при создании FinancialModel
* shortId должен быть уникален среди FinancialModel
* shortId имеет фиксированную длину 10 символов
* shortId не должен меняться при переименовании FinancialModel
* новая FinancialModel получает title и description из формы создания
* пользователь может изменить title и description после создания
* title не должен быть пустым
* versionNumber должен быть уникален в рамках одного User
* FinancialModel является самостоятельным сценарием: изменения в одной модели не должны менять данные другой модели
* скопированная FinancialModel должна получить независимую копию данных исходной модели
* sourceModelId не влияет на расчеты и используется только как информация о происхождении модели
* TimeParams можно изменять после создания FinancialModel
* изменение TimeParams меняет расчетный горизонт модели
* изменение TimeParams не должно автоматически удалять или переписывать данные других доменных блоков без отдельного явно описанного use case
* расчет FinancialModel выполняется на backend на основе сохраненного состояния модели
* доступные блоки FinancialModel определяются кодом приложения, а не пользовательскими настройками
* amountDisplayFormat влияет только на output/presentation и не меняет сохраненные денежные значения

### Жизненный цикл
* FinancialModel создается пользователем на экране `/models`
* при создании пользователь вводит title, description и обязательные TimeParams
* после создания FinancialModel получает статус active
* после создания FinancialModel доступна для редактирования
* пользователь заполняет доменные блоки модели по вкладкам
* каждый доменный блок сохраняется независимо
* FinancialModel может быть переименована
* FinancialModel может быть скопирована как новый независимый сценарий
* копирование FinancialModel будет отдельным future use case
* FinancialModel может быть рассчитана backend-ом
* FinancialModel может быть экспортирована в Excel
* FinancialModel может быть архивирована
* архивированная FinancialModel доступна для просмотра и Excel export в read-only режиме
* архивированная FinancialModel недоступна для редактирования
* архивированная FinancialModel может быть восстановлена
* архивированная FinancialModel может быть удалена окончательно

### Расчет и экспорт
Расчет FinancialModel — это backend-операция, которая собирает данные всех доменных блоков и строит расчетное представление модели.

Excel export — отдельная операция представления результата расчета. На первом этапе Excel export может быть основным способом получить расчетный результат, но расчетная модель не должна быть жестко связана с Excel.

Важно: расчет модели не должен быть жестко равен Excel export. Сейчас Excel может быть первым способом получить расчетный результат, но позже те же расчетные данные должны использоваться для summary, графиков и таблиц на frontend.

Расчетное представление FinancialModel должно развиваться как backend calculation snapshot, который может использоваться для UI-таблиц, summary, графиков, Excel export и будущего AI report/recommendations service.

### Открытые вопросы
* Нужен ли в будущем уровень Workspace для командной работы?
* Как именно будет работать копирование FinancialModel?

___

## TimeParams

### Назначение
TimeParams — обязательный доменный блок FinancialModel, который задает расчетный горизонт модели, инвестиционную фазу, коммерческую фазу и шаг прогнозирования.

FinancialModel не может быть создана без TimeParams, потому что остальные расчетные блоки должны опираться на единый временной горизонт.

TimeParams не является самостоятельной моделью вне FinancialModel. Он принадлежит конкретной FinancialModel и сохраняется независимо от других доменных блоков модели.

### Поля
* id — внутренняя identity блока временных параметров
* financialModelId — identity финансовой модели, которой принадлежат временные параметры
* investmentStartMonth — месяц и год начала инвестиций; внутри системы хранится как первое число месяца
* investmentDurationMonths — длительность инвестиционной фазы в месяцах
* commercialOperationDurationMonths — длительность коммерческой эксплуатации в месяцах
* forecastStep — шаг прогнозирования, выбранный пользователем
* createdAt — audit timestamp
* updatedAt — audit timestamp

### Производные значения
Эти значения не вводятся пользователем, а рассчитываются backend-ом на основе TimeParams:

* investmentStartDate — первое число месяца начала инвестиций
* investmentEndDate — последнее число последнего месяца инвестиционной фазы
* commercialOperationStartDate — первое число месяца, следующего за окончанием инвестиционной фазы
* commercialOperationEndDate — последнее число последнего месяца коммерческой фазы
* modelStartDate — начало расчетного горизонта, равно investmentStartDate
* modelEndDate — конец расчетного горизонта, равно commercialOperationEndDate
* monthlyPeriods — временной ряд по месяцам от modelStartDate до modelEndDate
* investmentActivityFlags — массив флагов инвестиционной деятельности по периодам
* operatingActivityFlags — массив флагов операционной деятельности по периодам
* operatingStartFlags — массив флагов начала операционной деятельности по периодам

### Зависимости / связи
* TimeParams принадлежит FinancialModel
* одна FinancialModel имеет ровно один TimeParams
* TimeParams используется другими доменными блоками как источник расчетного горизонта
* TimeParams не знает деталей Investments, EBITDA и других блоков
* изменение TimeParams влияет на расчетные представления модели, но не должно напрямую изменять данные других блоков

### Инварианты
* TimeParams не может существовать вне FinancialModel
* FinancialModel не может быть создана без TimeParams
* investmentStartMonth обязателен
* investmentStartMonth всегда нормализуется до первого числа месяца
* investmentDurationMonths обязателен и должен быть положительным целым числом
* commercialOperationDurationMonths обязателен и должен быть положительным целым числом
* forecastStep должен быть одним из допустимых значений ForecastStep
* на первом этапе forecastStep не меняет месячный расчетный ряд
* сохраненное значение forecastStep не должно ломать текущие расчеты, даже если выбрано quarter или year
* investmentEndDate всегда является последним числом месяца
* commercialOperationStartDate всегда является первым числом месяца
* commercialOperationEndDate всегда является последним числом месяца
* modelEndDate должен быть позже modelStartDate
* временной ряд не должен быть пустым
* расчетные даты, периоды и флаги должны строиться backend-ом на основе сохраненных TimeParams

### Правила расчета дат
Дата начала инвестиций задается пользователем как месяц и год. Внутри системы она всегда фиксируется как первое число выбранного месяца.

Если investmentDurationMonths = 1, инвестиционная фаза длится один календарный месяц: от первого числа месяца начала инвестиций до последнего числа этого же месяца.

Формулы:

* investmentStartDate = первое число investmentStartMonth
* investmentEndDate = конец месяца для даты investmentStartDate + (investmentDurationMonths - 1) месяцев
* commercialOperationStartDate = investmentEndDate + 1 день
* commercialOperationEndDate = конец месяца для даты commercialOperationStartDate + (commercialOperationDurationMonths - 1) месяцев
* modelStartDate = investmentStartDate
* modelEndDate = commercialOperationEndDate

### Правила построения временного ряда
TimeParams строит месячный временной ряд от modelStartDate до modelEndDate включительно.

Для каждого месяца рассчитываются:

* periodStartDate — первое число месяца
* periodEndDate — последнее число месяца

Первый период:

* periodStartDate = investmentStartDate
* periodEndDate = конец месяца investmentStartDate

Последующие периоды:

* periodStartDate = periodEndDate предыдущего периода + 1 день
* periodEndDate = конец месяца periodStartDate

### Правила расчета флагов
Для каждого периода временного ряда backend рассчитывает флаги активности.

investmentActivityFlag:

* 1, если periodStartDate >= investmentStartDate и periodStartDate <= investmentEndDate
* 0 в остальных случаях

operatingActivityFlag:

* 1, если periodStartDate >= commercialOperationStartDate
* 0 в остальных случаях

operatingStartFlag:

* 1, если periodStartDate = commercialOperationStartDate
* 0 в остальных случаях

### ForecastStep на первом этапе
Пользователь выбирает forecastStep при создании или редактировании TimeParams.

На первом этапе forecastStep сохраняется как доменная настройка модели, но не меняет базовый расчетный временной ряд.

Базовый расчетный ряд всегда строится помесячно:

* в интерфейсе
* в расчетных данных
* в Excel export

В Excel export формируется выпадающий список forecastStep. По умолчанию в нем выбран month.

Поддержка квартального и годового представления будет реализована позже как отдельное расширение расчетного и presentation layer.

### Жизненный цикл
* TimeParams создается вместе с FinancialModel
* пользователь может изменить TimeParams после создания FinancialModel
* после изменения TimeParams backend пересчитывает производные даты, временной ряд и флаги
* изменение TimeParams не удаляет и не переписывает данные других доменных блоков автоматически
* расчетные данные, summary, графики и Excel export должны строиться на основе актуальных TimeParams

### Открытые вопросы
* Как именно quarter/year должны агрегировать данные, когда будет реализована поддержка forecastStep?
___

## Investments

### Назначение
Investments — доменный блок FinancialModel, который описывает первоначальные инвестиции проекта, капитальные вложения, расходы будущих периодов, первоначальный оборотный капитал и график их финансирования.

Investments принадлежит конкретной FinancialModel и сохраняется отдельно от других блоков модели.

Investments является отдельным aggregate root, принадлежащим FinancialModel по жизненному циклу. Его данные используются backend-ом для расчета CAPEX, НЗС, амортизации, списания расходов будущих периодов, графика финансирования и Excel export.

### Поля
* id — внутренняя identity инвестиционного блока
* financialModelId — identity финансовой модели
* createdAt — audit timestamp
* updatedAt — audit timestamp

### Основные сущности внутри блока
* InvestmentBlock — корневой блок инвестиций внутри FinancialModel
* InvestmentItem — пользовательская инвестиционная строка/объект
* InvestmentCategory — code-defined категория инвестиции
* InvestmentSchedule — график распределения инвестиционной суммы по месяцам
* InvestmentSchedulePeriod — отметка периода, в котором выполняется финансирование

### InvestmentItem
InvestmentItem — конкретный инвестиционный объект или расход, введенный пользователем.

Поля:
* id — внутренняя identity строки
* investmentBlockId — identity блока Investments
* category — категория инвестиции
* title — наименование объекта или расхода
* initialCost — первоначальная стоимость, введенная пользователем
* vatApplied — признак, применяется ли НДС к строке
* usefulLifeYears — срок полезного использования в годах, если применимо
* deferredExpenseWriteOffYears — срок списания расходов будущих периодов в годах, если применимо
* leaseTermMonths — срок договора аренды в месяцах, если применимо
* depreciationEnabled — амортизируется ли объект, если категория допускает выбор
* propertyTaxable — облагается ли налогом на имущество, если применимо
* propertyTaxBase — база расчета налога на имущество, если применимо
* cadastralValue — кадастровая стоимость, если применимо
* landTaxable — облагается ли земельным налогом, если применимо
* vehicleTaxable — облагается ли транспортным налогом, если применимо
* vehiclePowerHp — мощность транспортного средства, если применимо

### Категории инвестиций
Категории Investments определяются кодом приложения, а не создаются пользователем произвольно.

Предварительный список:
* land — земельный участок
* buildings — здания и сооружения
* equipment — оборудование и приборы
* furnitureAndOfficeEquipment — мебель, оргтехника, офисное оборудование
* vehicles — транспортные средства
* intangibleAssets — нематериальные активы и ПО
* leaseholdImprovements — неотделимые улучшения арендованного имущества
* otherCapitalized — другое
* initialInventory — первоначальные запасы
* deferredMarketingAndTraining — расходы на рекламу, маркетинг, обучение и др.
* otherDeferredExpense — другое в расходах будущих периодов

### Расчетная классификация
Для расчетов категория должна иметь code-defined treatment:

* capexNonDepreciable — капитальные вложения без амортизации
* capexDepreciable — капитальные вложения с амортизацией
* deferredExpense — расходы будущих периодов
* initialWorkingCapital — первоначальный оборотный капитал

Эта классификация заменяет хранение строковых ключей вроде `CAPEX_амортизация` как свободного текста.

### НДС на первом этапе
На первом этапе ставка НДС является code-defined значением `22%`.

InvestmentItem хранит только признак применения НДС к строке. Полноценная налоговая модель будет спроектирована позже как отдельный доменный блок.

### Правила обязательности по категориям
* land — title, initialCost, landTaxable; cadastralValue обязателен, если landTaxable = taxable
* buildings — title, initialCost, vatApplied, propertyTaxable, usefulLifeYears; propertyTaxBase обязателен, если propertyTaxable = taxable; cadastralValue обязателен, если propertyTaxBase = cadastralValue
* equipment — title, initialCost, vatApplied, propertyTaxable, usefulLifeYears
* furnitureAndOfficeEquipment — title, initialCost, vatApplied, usefulLifeYears
* vehicles — title, initialCost, vatApplied, vehicleTaxable, usefulLifeYears; vehiclePowerHp обязателен, если vehicleTaxable = taxable
* intangibleAssets — title, initialCost, vatApplied, usefulLifeYears; амортизация обязательна
* leaseholdImprovements — initialCost, vatApplied, leaseTermMonths, propertyTaxable
* otherCapitalized — initialCost, vatApplied, depreciationEnabled; usefulLifeYears обязателен, если depreciationEnabled = true
* initialInventory — initialCost, vatApplied
* deferredMarketingAndTraining — initialCost, vatApplied, deferredExpenseWriteOffYears
* otherDeferredExpense — initialCost, vatApplied, deferredExpenseWriteOffYears

### График финансирования
Для каждой InvestmentItem пользователь выбирает месяцы инвестиционной фазы, в которых происходит финансирование.

Базовая логика:
* доступные периоды графика берутся из TimeParams
* используются только месяцы инвестиционной фазы
* пользователь отмечает один или несколько месяцев
* каждая отметка означает участие месяца в распределении суммы
* сумма платежа в каждом отмеченном месяце = initialCost / количество отмеченных месяцев
* итог финансирования периода = сумма платежей всех InvestmentItem в этом периоде

### Зависимости / связи
* Investments принадлежит FinancialModel
* одна FinancialModel может иметь не более одного Investments block
* Investments является отдельным aggregate root, связанным с FinancialModel
* Investments использует TimeParams как источник инвестиционных периодов
* Investments не изменяет TimeParams
* InvestmentItem принадлежит Investments
* InvestmentSchedule принадлежит InvestmentItem
* Investments используется расчетным backend-ом и Excel export

### Инварианты
* Investments не может существовать вне FinancialModel
* одна FinancialModel имеет не более одного Investments block
* InvestmentItem не может существовать вне Investments
* category должна быть одним из допустимых InvestmentCategory
* initialCost должна быть положительной денежной величиной
* title обязателен для категорий, где пользователь вводит наименование объекта
* полезный срок, срок списания и срок аренды должны быть положительными, если поле применимо
* график финансирования может содержать только периоды инвестиционной фазы
* на первом этапе инвестиции разрешены только в инвестиционной фазе
* для расчета FinancialModel и Excel export график финансирования InvestmentItem должен быть заполнен
* изменение TimeParams не должно автоматически удалять InvestmentItem или график финансирования
* если после изменения TimeParams часть графика вышла за пределы инвестиционной фазы, блок должен считаться требующим исправления
* расчеты CAPEX, НЗС, амортизации и списания выполняются backend-ом

### Жизненный цикл
* Investments создается для FinancialModel как отдельный доменный блок
* пользователь добавляет InvestmentItem в одну из code-defined категорий
* пользователь заполняет обязательные поля категории
* пользователь задает график финансирования по месяцам инвестиционной фазы
* InvestmentItem может существовать без заполненного графика финансирования как черновик
* пользователь может редактировать InvestmentItem
* пользователь может удалить InvestmentItem
* при копировании FinancialModel Investments копируется как независимый блок
* при расчете модели backend строит расчетные данные Investments на основе актуальных TimeParams

### Что намеренно не моделируем сейчас
* ремонтную программу
* полную налоговую модель
* редактируемую вручную таблицу финансирования, независимую от графика реализации
* квартальную/годовую агрегацию Investments
* произвольные пользовательские категории инвестиций

### Открытые вопросы
* Где будет храниться ставка НДС после появления полноценного Taxation block?
___

## Value Objects

* ShortId — короткий случайный публичный идентификатор сущности
    * используется для стабильных URL и внутренних публичных ссылок
    * не зависит от title
    * не меняется при переименовании сущности
    * генерируется backend-ом
    * проверяется на уникальность
    * имеет фиксированную длину 10 символов
    * использует lowercase-алфавит `23456789abcdefghjkmnpqrstuvwxyz`
    * исключает похожие символы: 0/O, 1/I/l
    * при collision application layer повторяет генерацию

* Money — денежная сумма в рублях
    * валюта всегда RUB
    * может иметь дробную часть
    * не хранится как float
    * формат отображения не должен менять хранимое значение
    * для инвестиционных сумм значение должно быть положительным

* YearMonth — месяц и год без пользовательского дня
    * внутри системы нормализуется к первому числу месяца
    * используется для даты начала инвестиций и построения месячного ряда

* MonthDuration — длительность в месяцах
    * положительное целое число
    * используется для длительности инвестиционной и коммерческой фаз

* ModelPeriod — расчетный период
    * имеет дату начала
    * имеет дату окончания
    * дата окончания не может быть раньше даты начала

## Enums

### FinancialModelStatus
* active — модель доступна для редактирования, расчета и экспорта
* archived — модель остается в общем списке после active-моделей, недоступна для редактирования, доступна для просмотра и Excel export, может быть восстановлена или удалена окончательно

### ForecastStep
* month — месячный шаг прогнозирования
* quarter — квартальный шаг прогнозирования
* year — годовой шаг прогнозирования

На первом этапе forecastStep сохраняется, но не меняет месячный расчетный ряд.

### InvestmentCategory
* land — земельный участок
* buildings — здания и сооружения
* equipment — оборудование и приборы
* furnitureAndOfficeEquipment — мебель, оргтехника, офисное оборудование
* vehicles — транспортные средства
* intangibleAssets — нематериальные активы и ПО
* leaseholdImprovements — неотделимые улучшения арендованного имущества
* otherCapitalized — другое в капитализируемых затратах
* initialInventory — первоначальные запасы
* deferredMarketingAndTraining — расходы на рекламу, маркетинг, обучение и др.
* otherDeferredExpense — другое в расходах будущих периодов

### InvestmentTreatment
* capexNonDepreciable — капитальные вложения без амортизации
* capexDepreciable — капитальные вложения с амортизацией
* deferredExpense — расходы будущих периодов
* initialWorkingCapital — первоначальный оборотный капитал

### TaxabilityStatus
* taxable — облагается налогом
* notTaxable — не облагается налогом

### PropertyTaxBase
* cadastralValue — кадастровая стоимость
* averageAnnualValue — среднегодовая стоимость

### UserRole
* user — обычный пользователь приложения
* admin — пользователь с доступом к админке
* superAdmin — пользователь с полным административным доступом

### AmountDisplayFormat
Настройка FinancialModel, определяющая, как выводить суммы в интерфейсе, summary, таблицах, Excel export и будущих отчетах.

* wholeRubles — показывать суммы как целые рубли
* decimalRubles — показывать суммы с дробной частью

## Совокупные границы

* Project — aggregate root для проектного контейнера
    * управляет названием, описанием, публичным идентификатором и статусом проекта
    * не управляет внутренними блоками FinancialModel

* FinancialModel — aggregate root для расчетного сценария
    * принадлежит Project
    * управляет названием, versionNumber, shortId, sourceModelId, статусом модели и amountDisplayFormat
    * задает границу сценария: изменения одной FinancialModel не влияют на другую

* TimeParams — обязательный доменный блок FinancialModel
    * концептуально является частью FinancialModel
    * сохраняется отдельным use case
    * задает расчетный горизонт, но не знает деталей Investments и других блоков

* Investments — отдельный aggregate root внутри FinancialModel
    * принадлежит FinancialModel
    * управляет инвестиционными строками, категориями и графиком финансирования
    * использует TimeParams как источник инвестиционных периодов
    * не изменяет TimeParams

* User — aggregate root для identity/access
    * владеет Project через ownerId
    * роли пользователя определяют доступ к пользовательскому интерфейсу и админке

* SiteSettings / ContentSettings — отдельная административная область
    * отвечает за настройки главного экрана, текстов, изображений, мета-тегов и статичного контента
    * не должна управлять списком финансовых вкладок как application features

## Admin / Content Settings

Админка относится к управлению доступом и presentation/content-настройками приложения, но не является источником финансовой бизнес-логики.

Административный доступ задается через роли User, а не через отдельную сущность AdminUser.

Админка может управлять:
* заголовком главного экрана
* изображением главного экрана
* статичными текстами на страницах
* meta title
* meta description
* вспомогательным контентом

Админка не должна управлять:
* списком доменных блоков FinancialModel
* списком финансовых вкладок как application features
* правилами расчета
* enum-значениями, от которых зависит расчетная логика
* storage model финансовых блоков

## То, что намеренно еще не моделируем

* Revenue / выручка
* OPEX как полноценный отдельный блок
* EBITDA как отдельный расчетный блок
* Financing / debt / equity structure
* Taxes как полноценный отдельный доменный блок
* Working capital как полноценный отдельный блок
* Repair program
* Scenario comparison UI
* Quarter/year forecast aggregation
* Chart endpoints
* Summary endpoints
* Full calculation engine outside Excel export
* Admin-configurable financial tabs
* Гибкую CMS для всех страниц
* Сложную permission model по проектам и командам

___
