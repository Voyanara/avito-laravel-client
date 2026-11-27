<?php

namespace Voyanara\LaravelApiClient\Application\Facades;

use DateTimeInterface;
use Voyanara\LaravelApiClient\Application\Actions\Items\ApplyItemVasAction;
use Voyanara\LaravelApiClient\Application\Actions\Items\ApplyItemVasPackageAction;
use Voyanara\LaravelApiClient\Application\Actions\Items\ApplyVasAction;
use Voyanara\LaravelApiClient\Application\Actions\Items\GetCallsStatsAction;
use Voyanara\LaravelApiClient\Application\Actions\Items\GetItemInfoAction;
use Voyanara\LaravelApiClient\Application\Actions\Items\GetItemsAction;
use Voyanara\LaravelApiClient\Application\Actions\Items\GetItemsAnalyticsAction;
use Voyanara\LaravelApiClient\Application\Actions\Items\GetItemsStatsAction;
use Voyanara\LaravelApiClient\Application\Actions\Items\GetSpendingsAction;
use Voyanara\LaravelApiClient\Application\Actions\Items\GetVasPricesAction;
use Voyanara\LaravelApiClient\Application\Actions\Items\UpdateItemPriceAction;
use Voyanara\LaravelApiClient\Domain\Enums\Items\AnalyticsGroupingEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\ItemStatsFieldEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\ItemStatusEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\SortOrderEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\SpendingTypeEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\StatsPeriodEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\VasEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\VasPackageEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\VasServiceEnum;
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TokenValidException;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ApplyItemVasResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ApplyVasPackageResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ApplyVasResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\CallsStatsResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ItemInfoResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ItemsAnalyticsResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ItemsListResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\ItemsStatsResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\SpendingsResponse;
use Voyanara\LaravelApiClient\Presentation\Responses\Items\VasPricesResponse;

readonly class Items
{
    public function __construct(
        protected GetItemsAction $getItemsAction,
        protected GetItemInfoAction $getItemInfoAction,
        protected UpdateItemPriceAction $updateItemPriceAction,
        protected GetVasPricesAction $getVasPricesAction,
        protected ApplyItemVasAction $applyItemVasAction,
        protected ApplyItemVasPackageAction $applyItemVasPackageAction,
        protected ApplyVasAction $applyVasAction,
        protected GetItemsStatsAction $getItemsStatsAction,
        protected GetItemsAnalyticsAction $getItemsAnalyticsAction,
        protected GetSpendingsAction $getSpendingsAction,
        protected GetCallsStatsAction $getCallsStatsAction,
    ) {}

    /**
     * Получение информации по объявлениям
     *
     * Возвращает список объявлений авторизованного пользователя — статус, категорию и ссылку на сайте.
     *
     * Внимание! В настоящий момент этот метод не работает с объявлениями сотрудников.
     * Он позволяет получить объявления только для пользователя, который указан владельцем
     * этого объявления. В случае сотрудника это будет главный аккаунт компании, для авторизованного
     * сотрудника вернётся пустой список объявлений.
     *
     * Максимальное количество запросов в минуту — 25.
     *
     * Описание параметров запроса:
     *
     * - per_page: Целое число (int32) больше 0 и меньше 100. По умолчанию: `25`. Пример: `per_page=50`.
     *   Количество записей на странице.
     *
     * - page: Целое число (int32) больше 0. По умолчанию: `1`. Пример: `page=1`.
     *   Номер страницы.
     *
     * - status: Строка. По умолчанию: `active`. Пример: `status=active,old`.
     *   Статус объявления на сайте (можно указать несколько значений через запятую):
     *   active, removed, old, blocked, rejected.
     *
     * - updatedAtFrom: Строка. Пример: `updatedAtFrom=2023-12-18`.
     *   Фильтр больше либо равно по дате обновления/редактирования объявления в формате YYYY-MM-DD.
     *
     * - category: Целое число (int32). Пример: `category=111`.
     *   Идентификатор категории объявления, см. справочник категорий:
     *   https://www.avito.st/s/openapi/catalog-categories.xml
     *
     * Authorizations:
     * - (items:info) AuthorizationCodeClientCredentials
     *
     * @param  int  $perPage  Количество записей на странице (от 1 до 99)
     * @param  int  $page  Номер страницы
     * @param  list<ItemStatusEnum>  $statuses  Статусы объявлений; пустой список — значение по умолчанию на стороне Авито (active)
     * @param  DateTimeInterface|string|null  $updatedAtFrom  Дата обновления объявления, начиная с которой (включительно) вернуть объявления
     * @param  int|null  $category  Идентификатор категории объявления
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://www.avito.ru/developers/api-catalog/item/documentation#operation/getItemsInfo
     */
    public function getItems(
        int $perPage = 25,
        int $page = 1,
        array $statuses = [],
        DateTimeInterface|string|null $updatedAtFrom = null,
        ?int $category = null,
    ): ItemsListResponse {
        return $this->getItemsAction->handle($perPage, $page, $statuses, $updatedAtFrom, $category);
    }

    /**
     * Получение информации по объявлению
     *
     * Возвращает данные об объявлении — его статус, список примененных услуг.
     *
     * Максимальное количество запросов в минуту — 500.
     *
     * Внимание: для получения статистики объявления должен использоваться метод
     * получения статистики по списку объявлений {@see Items::getItemsStats()}.
     *
     * Параметры пути запроса:
     *
     * - user_id: Обязательный параметр. Целое число (int64).
     *   Номер пользователя в Личном кабинете Авито.
     *
     * - item_id: Обязательный параметр. Целое число (int64).
     *   Идентификатор объявления на сайте.
     *
     * Authorizations:
     * - (items:info) AuthorizationCodeClientCredentials
     *
     * @param  int  $userId  Номер пользователя в Личном кабинете Авито
     * @param  int  $itemId  Идентификатор объявления на сайте
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://www.avito.ru/developers/api-catalog/item/documentation#operation/getItemInfo
     */
    public function getItemInfo(int $userId, int $itemId): ItemInfoResponse
    {
        return $this->getItemInfoAction->handle($userId, $itemId);
    }

    /**
     * Обновление цены объявления
     *
     * Обновляет цену товара по id.
     *
     * Максимальное количество запросов в минуту — 150.
     *
     * Внимание! Доступно для категорий Товары, Запчасти, Авто, Недвижимость (кроме краткосрочной).
     *
     * Параметры пути запроса:
     *
     * - item_id: Обязательный параметр. Целое число (int64).
     *   Идентификатор объявления на сайте.
     *
     * Параметры тела запроса:
     *
     * - price: Обязательный параметр. Целое число.
     *   Цена.
     *
     * Authorizations:
     * - AuthorizationCodeClientCredentials
     *
     * @param  int  $itemId  Идентификатор объявления на сайте
     * @param  int  $price  Новая цена
     * @return bool Успешно ли обновлена цена
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://www.avito.ru/developers/api-catalog/item/documentation#operation/updatePrice
     */
    public function updatePrice(int $itemId, int $price): bool
    {
        return $this->updateItemPriceAction->handle($itemId, $price);
    }

    /**
     * Получение информации о стоимости услуг продвижения и доступных значках
     *
     * Возвращает в ответ список объектов со следующей структурой:
     *
     * - itemId: идентификатор объявления.
     * - vas: список объектов, которые содержат информацию о стоимости дополнительных услуг
     *   и пакетов дополнительных услуг для каждого объявления:
     *   - slug: идентификатор услуги или пакета услуг, см. {@see VasServiceEnum};
     *   - price: цена в рублях с учетом скидки;
     *   - priceOld: цена в рублях до применения скидки.
     * - stickers: список объектов, которые содержат доступные для объявления значки:
     *   - id: идентификатор значка;
     *   - title: название значка;
     *   - description: описание значка.
     *
     * Параметры пути запроса:
     *
     * - user_id: Обязательный параметр. Целое число (int64).
     *   Номер пользователя в Личном кабинете Авито.
     *
     * Параметры тела запроса:
     *
     * - itemIds: Обязательный параметр. Массив целых чисел.
     *   Набор идентификаторов объявлений на сайте.
     *
     * Authorizations:
     * - (items:info) AuthorizationCodeClientCredentials
     *
     * @param  int  $userId  Номер пользователя в Личном кабинете Авито
     * @param  list<int>  $itemIds  Набор идентификаторов объявлений на сайте
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://www.avito.ru/developers/api-catalog/item/documentation#operation/vasPrices
     */
    public function getVasPrices(int $userId, array $itemIds): VasPricesResponse
    {
        return $this->getVasPricesAction->handle($userId, $itemIds);
    }

    /**
     * Применение дополнительных услуг
     *
     * Внимание: метод объявлен устаревшим и больше не поддерживается.
     * Вместо него используйте метод `/core/v2/items/{itemId}/vas/` — {@see Items::applyVas()}.
     *
     * Применение дополнительной услуги к объявлению, в ответе возвращает данные о примененной
     * услуге и сумму списания.
     *
     * Внимание: получение ошибки при выполнении этой операции не означает, что услуга точно
     * не была куплена. В этом случае рекомендуется подождать несколько минут и проверить,
     * что услуга отсутствует в списке применённых, а только затем повторить попытку.
     *
     * Параметры пути запроса:
     *
     * - user_id: Обязательный параметр. Целое число (int64).
     *   Номер пользователя в Личном кабинете Авито.
     *
     * - item_id: Обязательный параметр. Целое число (int64).
     *   Идентификатор объявления на сайте.
     *
     * Параметры тела запроса:
     *
     * - vas_id: Обязательный параметр. Строка.
     *   Идентификатор услуги: highlight — выделение объявления, xl — XL-объявление.
     *
     * Authorizations:
     * - (items:apply_vas) AuthorizationCodeClientCredentials
     *
     * @deprecated Метод объявлен Авито устаревшим, используйте {@see Items::applyVas()}.
     *
     * @param  int  $userId  Номер пользователя в Личном кабинете Авито
     * @param  int  $itemId  Идентификатор объявления на сайте
     * @param  VasEnum  $vas  Идентификатор услуги
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://www.avito.ru/developers/api-catalog/item/documentation#operation/putItemVas
     */
    public function applyItemVas(int $userId, int $itemId, VasEnum $vas): ApplyItemVasResponse
    {
        return $this->applyItemVasAction->handle($userId, $itemId, $vas);
    }

    /**
     * Применение пакета дополнительных услуг
     *
     * Внимание: метод объявлен устаревшим и больше не поддерживается.
     * Вместо него используйте метод `/core/v2/items/{itemId}/vas/` — {@see Items::applyVas()}.
     *
     * Применение пакета дополнительных услуг к объявлению, в ответе возвращает сумму списания.
     *
     * Внимание: получение ошибки при выполнении этой операции не означает, что пакет точно
     * не был куплен. В этом случае рекомендуется подождать несколько минут и проверить,
     * что пакет отсутствует в списке применённых, а только затем повторить попытку.
     *
     * Параметры пути запроса:
     *
     * - user_id: Обязательный параметр. Целое число (int64).
     *   Номер пользователя в Личном кабинете Авито.
     *
     * - item_id: Обязательный параметр. Целое число (int64).
     *   Идентификатор объявления на сайте.
     *
     * Параметры тела запроса:
     *
     * - package_id: Обязательный параметр. Строка.
     *   Идентификатор пакета услуг: x2_1, x2_7, x5_1, x5_7, x10_1, x10_7.
     *   В некоторых регионах и категориях также доступны x15_1, x15_7, x20_1, x20_7.
     *   Если попытаться применить эти пакеты в недоступных для них регионе и категории,
     *   оплата не пройдёт.
     *
     * Authorizations:
     * - (items:apply_vas) AuthorizationCodeClientCredentials
     *
     * @deprecated Метод объявлен Авито устаревшим, используйте {@see Items::applyVas()}.
     *
     * @param  int  $userId  Номер пользователя в Личном кабинете Авито
     * @param  int  $itemId  Идентификатор объявления на сайте
     * @param  VasPackageEnum  $package  Идентификатор пакета услуг
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://www.avito.ru/developers/api-catalog/item/documentation#operation/putItemVasPackageV2
     */
    public function applyItemVasPackage(int $userId, int $itemId, VasPackageEnum $package): ApplyVasPackageResponse
    {
        return $this->applyItemVasPackageAction->handle($userId, $itemId, $package);
    }

    /**
     * Применение услуг продвижения
     *
     * С помощью этого метода вы можете применить к опубликованному объявлению одну или несколько
     * услуг продвижения (например, «XL-объявление», «Выделение цветом» и «До 10 раз больше
     * просмотров на 7 дней»). В рамках одного запроса услуга может быть применена только один раз.
     *
     * Если для вашего объявления доступны значки (такие как «Без ДТП», «Срочно», «1 владелец»),
     * при подключении услуги «XL-объявление» вы можете передать их список (не более трёх значков).
     * В этом случае добавьте соответствующую услугу на 1, 2 или 3 значка.
     *
     * Чтобы получить список доступных услуг и значков, используйте метод
     * `/core/v1/accounts/{userId}/vas/prices` — {@see Items::getVasPrices()}.
     *
     * Если заказ сформирован успешно, в ответ вы получите уникальные идентификаторы операций покупки
     * для каждой из применяемых услуг. Позже эти идентификаторы можно будет использовать, чтобы
     * узнать статус выполнения заказа.
     *
     * В случае некорректного запроса метод вернет код ответа 400 и структуру, содержащую поле code:
     *
     * - 1001 — один или несколько заголовков неправильно передаются;
     * - 1002 — ошибка в URL;
     * - 1003 — неверный идентификатор объявления из запроса;
     * - 1004 — JSON из тела запроса не соответствует схеме или список идентификаторов услуг пустой;
     * - 1005 — объявление, к которому вы хотите применить услуги, неактивно;
     * - 1006 — неправильное количество выбранных значков для объявления. Убедитесь, что в списке
     *   идентификаторов услуг есть услуга для покупки значков и она совпадает с количеством
     *   выбранных значков: stickerpack_x1 — 1 значок, stickerpack_x2 — 2 значка, stickerpack_x3 — 3 значка;
     * - 1007 — некоторые из выбранных услуг не могут быть применены;
     * - 1008 — в объявлении появились обязательные поля, которые нужно заполнить.
     *   Отредактируйте объявление и попробуйте применить услугу снова;
     * - 1009 — в кошельке не хватает средств для покупки услуг;
     * - 1010 — вы пытались купить больше одной услуги увеличения просмотров;
     * - 1011 — вы пытались купить значки, недоступные для выбранного объявления.
     *
     * В случае внутренней ошибки на стороне Авито вернётся код ответа 500 и структура,
     * содержащая поле code:
     *
     * - 1000 — ошибка на стороне Авито, попробуйте позже или напишите в поддержку.
     *
     * Важно: если ответ пришёл без кода ошибки или его значения нет в списке выше — возможно,
     * услуга всё-таки была куплена. Подождите несколько минут: услуга продвижения появится в списке
     * применённых, а если нет — попробуйте оформить её снова.
     *
     * Параметры пути запроса:
     *
     * - item_id: Обязательный параметр. Целое число (int64).
     *   Идентификатор объявления на сайте.
     *
     * Параметры тела запроса:
     *
     * - slugs: Обязательный параметр. Массив строк.
     *   Список идентификаторов услуг.
     *
     * - stickers: Массив целых чисел.
     *   Список значков.
     *
     * Authorizations:
     * - (items:apply_vas) AuthorizationCodeClientCredentials
     *
     * @param  int  $itemId  Идентификатор объявления на сайте
     * @param  list<VasServiceEnum|string>  $services  Список услуг; строка — для идентификаторов, которых ещё нет в {@see VasServiceEnum}
     * @param  list<int>  $stickers  Идентификаторы значков (не более трёх)
     *
     * @throws ClientResponseException В том числе при кодах ошибок 1000–1011
     * @throws TokenValidException
     *
     * @link https://www.avito.ru/developers/api-catalog/item/documentation#operation/applyVas
     */
    public function applyVas(int $itemId, array $services, array $stickers = []): ApplyVasResponse
    {
        return $this->applyVasAction->handle($itemId, $services, $stickers);
    }

    /**
     * Получение статистики по списку объявлений
     *
     * Получение счетчиков по переданному списку объявлений пользователя.
     *
     * Внимание: в запросе может быть передано не более 200 идентификаторов объявлений.
     *
     * Внимание: глубина такого запроса ограничена 270 днями.
     *
     * Счетчики:
     *
     * - uniqViews — число уникальных пользователей, просмотревших объявление;
     * - uniqContacts — число уникальных пользователей, совершивших контакты [1];
     * - uniqFavorites — число уникальных пользователей, добавивших объявление в «избранное»;
     * - views, contacts, favorites — устарели, используйте uniqViews, uniqContacts и uniqFavorites.
     *
     * Группировка счетчиков [2] (поле periodGrouping):
     *
     * - day (по умолчанию) — без группировки;
     * - week — суммирование счетчиков за неделю, в поле date будет первый день недели;
     * - month — суммирование счетчиков за месяц, в поле date будет первый день месяца.
     *
     * Примечания:
     *
     * - [1]: Под контактом понимаются: запросы телефона продавца, начатый чат с продавцом
     *   по конкретному объявлению, отклик на резюме и пр.
     * - [2]: Группировка уникальных пользователей происходит только в рамках одного дня.
     *
     * Параметры пути запроса:
     *
     * - user_id: Обязательный параметр. Целое число (int64).
     *   Идентификатор пользователя (клиента).
     *
     * Параметры тела запроса:
     *
     * - itemIds: Обязательный параметр. Массив целых чисел.
     *   Набор идентификаторов объявлений на сайте.
     *
     * - dateFrom: Обязательный параметр. Строка.
     *   Дата (в формате YYYY-MM-DD), с которой (включительно) надо получить статистику.
     *
     * - dateTo: Обязательный параметр. Строка.
     *   Дата (в формате YYYY-MM-DD), по которую (включительно) надо получить статистику.
     *
     * - fields: Массив строк.
     *   Набор счетчиков, которые должны присутствовать в ответе.
     *
     * - periodGrouping: Строка. Период группировки: day, week, month.
     *
     * Authorizations:
     * - (stats:read) AuthorizationCodeClientCredentials
     *
     * @param  int  $userId  Идентификатор пользователя (клиента)
     * @param  list<int>  $itemIds  Идентификаторы объявлений (не более 200)
     * @param  DateTimeInterface|string  $dateFrom  Дата начала периода (включительно), строка в формате YYYY-MM-DD
     * @param  DateTimeInterface|string  $dateTo  Дата конца периода (включительно), строка в формате YYYY-MM-DD
     * @param  list<ItemStatsFieldEnum>  $fields  Счетчики, которые должны присутствовать в ответе
     * @param  StatsPeriodEnum|null  $periodGrouping  Период группировки; null — по дням (значение по умолчанию на стороне Авито)
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://www.avito.ru/developers/api-catalog/item/documentation#operation/itemStatsShallow
     */
    public function getItemsStats(
        int $userId,
        array $itemIds,
        DateTimeInterface|string $dateFrom,
        DateTimeInterface|string $dateTo,
        array $fields = [],
        ?StatsPeriodEnum $periodGrouping = null,
    ): ItemsStatsResponse {
        return $this->getItemsStatsAction->handle($userId, $itemIds, $dateFrom, $dateTo, $fields, $periodGrouping);
    }

    /**
     * Получение статистических показателей по профилю
     *
     * Используйте группировки, чтобы получать конкретную статистику. Например, можете запросить
     * только общие значения показателей, а если нужны подробности, — значения показателей
     * по каждому объявлению или за определённый период. Доступные группировки:
     *
     * - totals — по общему значению показателя за определённый период, без детализации;
     * - item — по объявлениям;
     * - day — по дням;
     * - week — по неделям;
     * - month — по месяцам.
     *
     * Доступные показатели по объявлениям:
     *
     * - Основные: views, contacts, contactsShowPhone, contactsMessenger, contactsShowPhoneAndMessenger,
     *   contactsSbcDiscount, viewsToContactsConversion, favorites, averageViewCost, averageContactCost,
     *   impressions, impressionsToViewsConversion;
     * - Целевые действия: clickPackages, jobContacts;
     * - Заказы с Авито Доставкой: viewsToOrderedItemsConversion, orderedItems, orderedItemsPrice,
     *   deliveredItems, deliveredItemsPrice;
     * - Посуточная аренда: bookingPlacedCount, bookingPlacedPrice, bookingApprovedCount,
     *   bookingApprovedPrice, bookingAcceptedCount, bookingAcceptedPrice;
     * - Расходы: allSpending, spending, presenceSpending, promoSpending, restSpending, commission, spendingBonus;
     * - Количество объявлений за период: activeItems, newActiveItems, oldActiveItems.
     *
     * Суммы (стоимость заказов, бронирований и расходы) возвращаются в копейках.
     * Подробное описание каждого показателя — в документации Авито.
     *
     * Примечания:
     *
     * - Из ручки возвращается не более чем по 1000 сущностей. Вы можете использовать поля запроса
     *   limit и offset для регулировки выбранного диапазона.
     * - Глубина данных статистики такого запроса ограничена 270 днями.
     * - В случае недоступности метрики для пользователя она не приходит в ответе.
     * - Система позволяет делать не более одного запроса в минуту на данный метод.
     *
     * Параметры пути запроса:
     *
     * - user_id: Обязательный параметр. Целое число (int64).
     *   Идентификатор пользователя (клиента).
     *
     * Параметры тела запроса:
     *
     * - dateFrom: Обязательный параметр. Строка (date).
     *   Дата (в формате YYYY-MM-DD), с которой (включительно) надо получить статистику.
     *
     * - dateTo: Обязательный параметр. Строка (date).
     *   Дата (в формате YYYY-MM-DD), по которую (включительно) надо получить статистику.
     *
     * - metrics: Обязательный параметр. Массив строк.
     *   Набор доступных показателей, которые должны присутствовать в ответе.
     *
     * - grouping: Обязательный параметр. Строка: day, week, month, item, totals.
     *
     * - limit: Обязательный параметр. Целое число от 0 до 1000.
     *   Инструмент пагинации для ограничения количества сущностей в ответе.
     *
     * - offset: Обязательный параметр. Целое число от 0.
     *   Инструмент пагинации или смещение, с которого начинается выборка данных.
     *
     * - filter: Объект. Набор ограничений, по которым нужно отфильтровать данные:
     *   - categoryIDs: Массив целых чисел. Идентификаторы категорий;
     *   - employeeIDs: Массив целых чисел. Идентификаторы сотрудников.
     *
     * - sort: Объект. Сортировка по заданному показателю:
     *   - key: Обязательный параметр. Строка. Показатель статистики, по которому нужно отсортировать;
     *   - order: Обязательный параметр. Строка. Порядок сортировки: asc, desc.
     *
     * Authorizations:
     * - (stats:read) AuthorizationCodeClientCredentials
     *
     * @param  int  $userId  Идентификатор пользователя (клиента)
     * @param  DateTimeInterface|string  $dateFrom  Дата начала периода (включительно), строка в формате YYYY-MM-DD
     * @param  DateTimeInterface|string  $dateTo  Дата конца периода (включительно), строка в формате YYYY-MM-DD
     * @param  list<string>  $metrics  Показатели, которые должны присутствовать в ответе (views, contacts, ...)
     * @param  AnalyticsGroupingEnum  $grouping  Группировка показателей
     * @param  int  $limit  Количество сущностей в ответе (от 0 до 1000)
     * @param  int  $offset  Смещение, с которого начинается выборка
     * @param  list<int>  $categoryIds  Фильтр по идентификаторам категорий
     * @param  list<int>  $employeeIds  Фильтр по идентификаторам сотрудников
     * @param  string|null  $sortKey  Показатель, по которому нужно отсортировать; null — без сортировки
     * @param  SortOrderEnum  $sortOrder  Порядок сортировки (используется вместе с $sortKey)
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://www.avito.ru/developers/api-catalog/item/documentation#operation/itemAnalytics
     */
    public function getItemsAnalytics(
        int $userId,
        DateTimeInterface|string $dateFrom,
        DateTimeInterface|string $dateTo,
        array $metrics,
        AnalyticsGroupingEnum $grouping = AnalyticsGroupingEnum::TOTALS,
        int $limit = 1000,
        int $offset = 0,
        array $categoryIds = [],
        array $employeeIds = [],
        ?string $sortKey = null,
        SortOrderEnum $sortOrder = SortOrderEnum::DESC,
    ): ItemsAnalyticsResponse {
        return $this->getItemsAnalyticsAction->handle(
            $userId,
            $dateFrom,
            $dateTo,
            $metrics,
            $grouping,
            $limit,
            $offset,
            $categoryIds,
            $employeeIds,
            $sortKey,
            $sortOrder,
        );
    }

    /**
     * Получение статистики расходов профиля
     *
     * Внимание: глубина данных статистики такого запроса ограничена 270 днями.
     *
     * Внимание: система позволяет делать не более одного запроса в минуту на данный метод.
     *
     * Группировки расходов:
     *
     * - day — по дням;
     * - week — по неделям;
     * - month — по месяцам.
     *
     * Доступные типы расходов:
     *
     * - all — все расходы: сколько всего денег вы потратили на объявления и прочие услуги;
     *   - promotion — расходы на продвижение объявлений и услуги, которые влияют на внешний вид объявления;
     *   - presence — расходы на размещение объявлений и целевые действия с ними — просмотры, чаты, звонки и отклики;
     *   - commission — комиссия за заказы с Авито Доставкой, которые приняли покупатели, а также за бронирование жилья;
     *   - rest — остальные расходы: чат-боты и услуги, которые система не смогла распознать.
     *
     * Параметры пути запроса:
     *
     * - user_id: Обязательный параметр. Целое число (int64).
     *   Идентификатор пользователя (клиента).
     *
     * Параметры тела запроса:
     *
     * - dateFrom: Обязательный параметр. Строка (date).
     *   Дата начала периода статистики расходов в формате YYYY-MM-DD.
     *
     * - dateTo: Обязательный параметр. Строка (date).
     *   Дата конца периода статистики расходов в формате YYYY-MM-DD.
     *
     * - spendingTypes: Обязательный параметр. Массив строк.
     *   Набор необходимых типов расходов (all, promotion, presence, commission, rest).
     *
     * - grouping: Обязательный параметр. Строка.
     *   Группировка статистики расходов: day, week, month.
     *
     * - filter: Объект. Набор ограничений, по которым необходимо отфильтровать расходы:
     *   - categoryIDs: Массив целых чисел. Идентификаторы категорий;
     *   - itemIDs: Массив целых чисел. Идентификаторы объявлений;
     *   - locationIDs: Массив целых чисел. Идентификаторы населенных пунктов.
     *
     * Authorizations:
     * - (stats:read) AuthorizationCodeClientCredentials
     *
     * @param  int  $userId  Идентификатор пользователя (клиента)
     * @param  DateTimeInterface|string  $dateFrom  Дата начала периода, строка в формате YYYY-MM-DD
     * @param  DateTimeInterface|string  $dateTo  Дата конца периода, строка в формате YYYY-MM-DD
     * @param  list<SpendingTypeEnum|string>  $spendingTypes  Типы расходов
     * @param  StatsPeriodEnum  $grouping  Группировка расходов
     * @param  list<int>  $categoryIds  Фильтр по идентификаторам категорий
     * @param  list<int>  $itemIds  Фильтр по идентификаторам объявлений
     * @param  list<int>  $locationIds  Фильтр по идентификаторам населенных пунктов
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://www.avito.ru/developers/api-catalog/item/documentation#operation/accountSpendings
     */
    public function getSpendings(
        int $userId,
        DateTimeInterface|string $dateFrom,
        DateTimeInterface|string $dateTo,
        array $spendingTypes = [SpendingTypeEnum::ALL],
        StatsPeriodEnum $grouping = StatsPeriodEnum::DAY,
        array $categoryIds = [],
        array $itemIds = [],
        array $locationIds = [],
    ): SpendingsResponse {
        return $this->getSpendingsAction->handle(
            $userId,
            $dateFrom,
            $dateTo,
            $spendingTypes,
            $grouping,
            $categoryIds,
            $itemIds,
            $locationIds,
        );
    }

    /**
     * Получение статистики по звонкам
     *
     * Получение агрегированной статистики звонков, полученных пользователем.
     *
     * Параметры пути запроса:
     *
     * - user_id: Обязательный параметр. Целое число (int64).
     *   Номер пользователя в Личном кабинете Авито.
     *
     * Параметры тела запроса:
     *
     * - dateFrom: Обязательный параметр. Строка.
     *   Начальная дата периода (YYYY-MM-DD).
     *
     * - dateTo: Обязательный параметр. Строка.
     *   Конечная дата периода (YYYY-MM-DD).
     *
     * - itemIds: Массив целых чисел.
     *   Идентификаторы объявлений.
     *
     * Поля элемента ответа:
     *
     * - itemId: идентификатор объявления (0 означает, что звонок не атрибутирован до объявления);
     * - employeeId: идентификатор сотрудника в сервисе иерархии аккаунтов
     *   (0 означает, что звонок не атрибутирован до сотрудника);
     * - days: статистика звонков объявления в разрезе дней — calls (звонки всего), answered (отвеченные),
     *   new (новые), newAnswered (новые и одновременно отвеченные).
     *
     * Authorizations:
     * - (items:info) AuthorizationCodeClientCredentials
     *
     * @param  int  $userId  Номер пользователя в Личном кабинете Авито
     * @param  DateTimeInterface|string  $dateFrom  Начальная дата периода, строка в формате YYYY-MM-DD
     * @param  DateTimeInterface|string  $dateTo  Конечная дата периода, строка в формате YYYY-MM-DD
     * @param  list<int>  $itemIds  Идентификаторы объявлений; пустой список — по всем объявлениям
     *
     * @throws ClientResponseException
     * @throws TokenValidException
     *
     * @link https://www.avito.ru/developers/api-catalog/item/documentation#operation/postCallsStats
     */
    public function getCallsStats(
        int $userId,
        DateTimeInterface|string $dateFrom,
        DateTimeInterface|string $dateTo,
        array $itemIds = [],
    ): CallsStatsResponse {
        return $this->getCallsStatsAction->handle($userId, $dateFrom, $dateTo, $itemIds);
    }
}
