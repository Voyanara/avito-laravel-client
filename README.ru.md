<p align="center">
  <img width="70%" src="https://github.com/user-attachments/assets/b794813c-0dd0-4569-9b42-322980ea0968" alt="Avito Laravel Client">
</p>

<p align="center">
  <a href="https://github.com/Voyanara/avito-laravel-client/actions/workflows/tests.yml"><img src="https://img.shields.io/github/actions/workflow/status/Voyanara/avito-laravel-client/tests.yml?branch=main&style=flat-square&label=tests" alt="Тесты"></a>
  <a href="https://packagist.org/packages/voyanara/avito-laravel-client"><img src="https://img.shields.io/packagist/v/voyanara/avito-laravel-client.svg?style=flat-square&label=packagist" alt="Последняя версия на Packagist"></a>
  <a href="https://packagist.org/packages/voyanara/avito-laravel-client"><img src="https://img.shields.io/packagist/dt/voyanara/avito-laravel-client.svg?style=flat-square" alt="Загрузки"></a>
  <a href="https://packagist.org/packages/voyanara/avito-laravel-client"><img src="https://img.shields.io/packagist/php-v/voyanara/avito-laravel-client.svg?style=flat-square" alt="Версия PHP"></a>
  <img src="https://img.shields.io/badge/Laravel-12%20|%2013-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 12 | 13">
  <a href="LICENSE"><img src="https://img.shields.io/packagist/l/voyanara/avito-laravel-client.svg?style=flat-square" alt="Лицензия"></a>
</p>

<p align="center">
  <a href="README.md">English</a> · <b>Русский</b>
</p>

# Avito Laravel Client

Типизированный Laravel SDK для [API Авито](https://www.avito.ru/developers/): отвечайте клиентам в мессенджере Авито, управляйте вебхуками, объявлениями, ценами и продвижением, получайте статистику объявлений и расходов, данные аккаунта, баланс и историю операций. OAuth-токен пакет получает и обновляет сам, а ответы приходят в виде неизменяемых DTO на базе [spatie/laravel-data](https://github.com/spatie/laravel-data).

```php
$me = AvitoClient::user()->self();

$chats = AvitoClient::messenger()->getChats($me->id, unreadOnly: true);

foreach ($chats->chats as $chat) {
    AvitoClient::messenger()->sendMessage($me->id, $chat->id, 'Здравствуйте! Скоро ответим.');
    AvitoClient::messenger()->readChat($me->id, $chat->id);
}
```

## Содержание

- [Возможности](#возможности)
- [Требования](#требования)
- [Установка](#установка)
- [Настройка](#настройка)
- [Использование](#использование)
- [Покрытие API](#покрытие-api)
- [Рецепты](#рецепты)
- [Авторизация и хранение токена](#авторизация-и-хранение-токена)
- [Обработка ошибок](#обработка-ошибок)
- [Тесты](#тесты)
- [Дорожная карта](#дорожная-карта)
- [История изменений](#история-изменений)
- [Как помочь проекту](#как-помочь-проекту)
- [Лицензия](#лицензия)

## Возможности

- **Полное покрытие API мессенджера** — все 13 методов: чаты, сообщения, изображения, голосовые, чёрный список, вебхуки.
- **Полное покрытие API объявлений** — все 11 методов: объявления, цены, услуги продвижения, статистика объявлений, показатели профиля, расходы и звонки.
- **API аккаунта** — профиль, баланс и история операций.
- **Авторизация без настройки** — токен `client_credentials` запрашивается при первом обращении, кешируется и автоматически обновляется, если Авито вернул `401`/`403`.
- **Сменное хранилище токена** — файл (по умолчанию), база данных или своя реализация `TokenStorageInterface`.
- **Типизированные ответы** — каждый метод возвращает readonly DTO с camelCase-свойствами, а не сырой массив.
- **Нативно для Laravel** — автоматическая регистрация провайдера, фасад, внедрение зависимостей. Внутри используется клиент `Http`, поэтому в ваших тестах работает `Http::fake()`.

## Требования

| Версия пакета | PHP | Laravel |
|---|---|---|
| `1.4.x` | 8.3 · 8.4 · 8.5 | 12 · 13 |
| `1.3.x` | 8.3 · 8.4 · 8.5 | 12 · 13 |
| `1.2.x` | 8.3 · 8.4 · 8.5 | 11 · 12 · 13 |

> [!NOTE]
> Laravel 11 больше не поддерживается командой Laravel, а свежие версии Composer отказываются его устанавливать из-за неисправленных уязвимостей. Если вы ещё на Laravel 11, используйте `1.2.x`: `composer require voyanara/avito-laravel-client:^1.2`.

Также нужен аккаунт Авито с доступом к API и пара `client_id` / `client_secret` из [кабинета разработчика Авито](https://www.avito.ru/developers/).

## Установка

```bash
composer require voyanara/avito-laravel-client
```

Сервис-провайдер и фасад `AvitoClient` регистрируются автоматически через package discovery.

Пакет работает сразу с переменными из `.env`. Публиковать конфиг нужно, только если хотите его изменить:

```bash
php artisan vendor:publish --provider="Voyanara\LaravelApiClient\Infrastructure\Providers\AvitoModuleServiceProvider" --tag=config
```

Если хотите хранить токен в базе данных, опубликуйте и примените миграцию:

```bash
php artisan vendor:publish --provider="Voyanara\LaravelApiClient\Infrastructure\Providers\AvitoModuleServiceProvider" --tag=migrations
php artisan migrate
```

## Настройка

Добавьте ключи в `.env`:

```dotenv
AVITO_CLIENT_ID=your-client-id
AVITO_SECRET=your-client-secret

# Необязательно
AVITO_API_URL=https://api.avito.ru
AVITO_TOKEN_STORAGE=file          # file | database
TOKEN_FILE_NAME=avito-token       # хранилище file: storage/app/avito_module/avito-token.json
AVITO_RETRY_TIMES=1               # сколько раз повторить GET-запрос при 429, 0 — не повторять
AVITO_RETRY_MAX_WAIT=5            # самый долгий Retry-After (секунды), который стоит ждать
AVITO_TIMEOUT=30                  # сколько секунд ждать ответа
AVITO_CONNECT_TIMEOUT=10          # сколько секунд ждать соединения
```

| Ключ | Переменная окружения | По умолчанию | Описание |
|---|---|---|---|
| `api_url` | `AVITO_API_URL` | `https://api.avito.ru` | Базовый URL API Авито |
| `client_id` | `AVITO_CLIENT_ID` | — | Client ID приложения |
| `secret` | `AVITO_SECRET` | — | Client secret приложения |
| `token_storage` | `AVITO_TOKEN_STORAGE` | `file` | Где кешировать токен: `file` или `database` |
| `file_name` | `TOKEN_FILE_NAME` | `avito-token` | Имя файла для хранилища `file` (сохраняется в `storage/app/avito_module/{name}.json`) |
| `retry.times` | `AVITO_RETRY_TIMES` | `1` | Сколько раз повторить GET-запрос, отклонённый с `429`; POST и PUT не повторяются никогда |
| `retry.max_wait` | `AVITO_RETRY_MAX_WAIT` | `5` | Если `Retry-After` дольше (в секундах), сразу бросается `TooManyRequestsException` |
| `timeout` | `AVITO_TIMEOUT` | `30` | Сколько секунд ждать ответа Авито |
| `connect_timeout` | `AVITO_CONNECT_TIMEOUT` | `10` | Сколько секунд ждать соединения с Авито |

## Использование

Точка входа — класс `Voyanara\LaravelApiClient\Application\Facades\Client`, у него три группы методов:

- `->user()` — API аккаунта;
- `->messenger()` — API мессенджера;
- `->items()` — API объявлений (объявления, продвижение, статистика).

### Внедрение зависимостей (рекомендуется)

```php
use Voyanara\LaravelApiClient\Application\Facades\Client;

class AvitoInboxController
{
    public function __invoke(Client $avito)
    {
        $userId = $avito->user()->self()->id;

        return $avito->messenger()->getChats($userId, limit: 20);
    }
}
```

### Фасад

```php
use Voyanara\LaravelApiClient\Application\Facades\AvitoClientFacade as AvitoClient;

$balance = AvitoClient::user()->getBalance($userId);

echo "Основной: {$balance->real} ₽, бонусный: {$balance->bonus} ₽";
```

> Большинству методов Авито нужен ID вашего пользователя. Получите его один раз через `user()->self()->id` и сохраните, например, в конфиге.

## Покрытие API

### Аккаунт — `$client->user()`

| Метод | Возвращает | Описание |
|---|---|---|
| `self()` | `UserInfoSelfResponse` | Авторизованный аккаунт: `id`, `name`, `email`, `phone`, `profileUrl` |
| `getBalance(int $userId)` | `UserInfoBalanceResponse` | Баланс кошелька: `real` и `bonus` |
| `getOperationsHistory(string $dateTimeFrom, string $dateTimeTo)` | `UserInfoOperationsResponse` | Операции за период (ISO 8601; не больше недели и не старше года) |

### Мессенджер — `$client->messenger()`

Полное покрытие [API мессенджера Авито](https://www.avito.ru/developers/api-catalog/messenger/documentation):

| Метод | Описание | Scope |
|---|---|---|
| `getChats(int $userId, int $limit = 10, ?bool $unreadOnly = null, array $itemIds = [], array $chatTypes = [], int $offset = 0)` | Список чатов; `chatTypes` (`ChatTypeEnum` или строка): `u2i` (по объявлениям), `u2u` (между пользователями), `a2u` (между пользователями и Авито) | `messenger:read` |
| `chatInfo(int $userId, string $chatId)` | Информация о чате и последнее сообщение | `messenger:read` |
| `messagesListFromChat(int $userId, string $chatId, int $limit = 10, int $offset = 0)` | Сообщения чата (не помечает чат прочитанным) | `messenger:read` |
| `sendMessage(int $userId, string $chatId, string $message, string $type = 'text')` | Отправить текстовое сообщение | `messenger:write` |
| `uploadImage(int $userId, string $filePath)` | Загрузить изображение (JPEG, HEIC, GIF, BMP, PNG; до 24 МБ) | `messenger:write` |
| `sendMessageWithImage(int $userId, string $chatId, string $imageId)` | Отправить загруженное изображение | `messenger:write` |
| `deleteMessage(int $userId, string $chatId, string $messageId)` | Удалить сообщение (в течение часа после отправки) | `messenger:write` |
| `readChat(int $userId, string $chatId)` | Пометить чат прочитанным | `messenger:read` |
| `getVoiceFiles(int $userId, array $voiceIds)` | Ссылки на голосовые сообщения (действуют один час) | `messenger:read` |
| `addUserToBlacklist(int $userId, int $blockedUserId, ?int $itemId = null, BlacklistReasonEnum\|int\|null $reasonId = null)` | Заблокировать пользователя (`BlacklistReasonEnum` или число: `1` спам, `2` мошенничество, `3` оскорбления, `4` другое) | `messenger:write` |
| `subscribeWebhook(string $url)` | Подписаться на вебхуки (v3) | `messenger:read` |
| `unsubscribeWebhook(string $url)` | Отписаться от вебхуков | `messenger:read` |
| `getSubscriptions()` | Список подписок на вебхуки | `messenger:read` |

> [!IMPORTANT]
> С ноября 2025 года для чтения и отправки сообщений Авито требует платную подписку с доступом к API мессенджера. Без неё API отвечает ошибкой `402` («Перейдите на подписку с API мессенджера»). Список чатов, информация о чате и управление вебхуками остаются доступны.

### Объявления — `$client->items()`

Полное покрытие [API объявлений Авито](https://www.avito.ru/developers/api-catalog/item/documentation). Фиксированные наборы значений — backed enums в `Voyanara\LaravelApiClient\Domain\Enums\Items`; даты принимают `DateTimeInterface` (например, Carbon) или строку `YYYY-MM-DD`.

| Метод | Описание | Scope |
|---|---|---|
| `getItems(int $perPage = 25, int $page = 1, array $statuses = [], DateTimeInterface\|string\|null $updatedAtFrom = null, ?int $category = null)` | Ваши объявления: статус, категория, цена и ссылка (фильтр `ItemStatusEnum`) | `items:info` |
| `getItemInfo(int $userId, int $itemId)` | Одно объявление: статус, даты, примененные платные услуги | `items:info` |
| `updatePrice(int $itemId, int $price)` | Обновление цены (Товары, Запчасти, Авто, Недвижимость, кроме краткосрочной) | — |
| `getVasPrices(int $userId, array $itemIds)` | Стоимость услуг продвижения и доступные значки по объявлениям | `items:info` |
| `applyVas(int $itemId, array $services, array $stickers = [])` | Применение услуг продвижения (`VasServiceEnum`), возвращает идентификаторы операций покупки | `items:apply_vas` |
| `applyItemVas(int $userId, int $itemId, VasEnum $vas)` | **Устарел в Авито**, используйте `applyVas()` | `items:apply_vas` |
| `applyItemVasPackage(int $userId, int $itemId, VasPackageEnum $package)` | **Устарел в Авито**, используйте `applyVas()` | `items:apply_vas` |
| `getItemsStats(int $userId, array $itemIds, $dateFrom, $dateTo, array $fields = [], ?StatsPeriodEnum $periodGrouping = null)` | Просмотры, контакты и избранное по объявлениям (до 200 объявлений, глубина 270 дней) | `stats:read` |
| `getItemsAnalytics(int $userId, $dateFrom, $dateTo, array $metrics, AnalyticsGroupingEnum $grouping = TOTALS, int $limit = 1000, int $offset = 0, ...)` | Показатели профиля: просмотры, контакты, конверсии, заказы, расходы (1 запрос в минуту) | `stats:read` |
| `getSpendings(int $userId, $dateFrom, $dateTo, array $spendingTypes = [SpendingTypeEnum::ALL], StatsPeriodEnum $grouping = DAY, ...)` | Расходы профиля по дням, неделям или месяцам (1 запрос в минуту) | `stats:read` |
| `getCallsStats(int $userId, $dateFrom, $dateTo, array $itemIds = [])` | Звонки по объявлениям в разрезе дней: всего, отвеченные, новые | `items:info` |

## Рецепты

### Автоответ на непрочитанные чаты

```php
$userId = $client->user()->self()->id;

$unread = $client->messenger()->getChats($userId, limit: 50, unreadOnly: true);

foreach ($unread->chats as $chat) {
    $client->messenger()->sendMessage($userId, $chat->id, 'Спасибо за сообщение! Менеджер ответит в течение 15 минут.');
    $client->messenger()->readChat($userId, $chat->id);
}
```

Поставьте это в задачу по расписанию (`Schedule::job(...)->everyMinute()`), и получится простой автоответчик.

### История переписки

```php
$history = $client->messenger()->messagesListFromChat($userId, $chatId, limit: 100);

foreach ($history->messages as $message) {
    $who = $message->direction === 'in' ? 'Клиент' : 'Вы';
    echo "{$who}: ".($message->content['text'] ?? "[{$message->type}]").PHP_EOL;
}
```

### Отправка фото

```php
$upload = $client->messenger()->uploadImage($userId, storage_path('app/catalog/sofa.jpg'));

$client->messenger()->sendMessageWithImage($userId, $chatId, $upload->id);
```

### Вебхуки

```php
$client->messenger()->subscribeWebhook(route('avito.webhook'));

foreach ($client->messenger()->getSubscriptions()->subscriptions as $subscription) {
    echo "{$subscription->url} (v{$subscription->version})".PHP_EOL;
}

$client->messenger()->unsubscribeWebhook(route('avito.webhook'));
```

Авито будет отправлять `POST`-запрос на ваш URL при каждом новом сообщении, так что реагировать можно сразу, без постоянного опроса API.

### Баланс и расходы

```php
$history = $client->user()->getOperationsHistory(
    now()->subWeek()->toIso8601String(),
    now()->toIso8601String(),
);

$spent = $history->operations->sum('amountTotal');
```

### Объявления, цены и продвижение

```php
use Voyanara\LaravelApiClient\Domain\Enums\Items\ItemStatusEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\VasServiceEnum;

$items = $client->items()->getItems(perPage: 50, statuses: [ItemStatusEnum::ACTIVE]);

foreach ($items->resources as $item) {
    echo "{$item->id} {$item->title}: {$item->price} ₽".PHP_EOL;
}

$client->items()->updatePrice($itemId, 14500);

$prices = $client->items()->getVasPrices($userId, [$itemId])->forItem($itemId);

$order = $client->items()->applyVas($itemId, [VasServiceEnum::XL, VasServiceEnum::X10_7]);
$operationId = $order->operationId(VasServiceEnum::XL);
```

### Статистика объявлений

```php
use Voyanara\LaravelApiClient\Domain\Enums\Items\AnalyticsGroupingEnum;
use Voyanara\LaravelApiClient\Domain\Enums\Items\StatsPeriodEnum;

$stats = $client->items()->getItemsStats($userId, [$itemId], now()->subDays(30), now(), periodGrouping: StatsPeriodEnum::WEEK);

$views = $stats->forItem($itemId)?->stats->sum('uniqViews');

$analytics = $client->items()->getItemsAnalytics(
    $userId,
    now()->subDays(30),
    now(),
    metrics: ['views', 'contacts', 'favorites'],
    grouping: AnalyticsGroupingEnum::ITEM,
);

foreach ($analytics->groupings as $row) {
    echo "{$row->id}: {$row->metric('views')} просмотров, {$row->metric('contacts')} контактов".PHP_EOL;
}
```

## Авторизация и хранение токена

Пакет использует OAuth 2.0 с типом `client_credentials`:

1. При первом вызове API клиент запрашивает токен через `POST /token`, передавая `client_id` и `client_secret`. Само получение `Client` из контейнера HTTP-запросов не делает.
2. Токен кешируется в выбранном хранилище, чтобы не запрашивать новый на каждый запрос.
3. Если Авито отвечает `401` или `403`, запрос один раз повторяется с новым токеном. Если другой репозиторий или процесс уже обновил токен в хранилище, берётся он, и `/token` повторно не вызывается. Токен, полученный меньше минуты назад, не обновляется: Авито отклонил его по другой причине (например, нет нужного scope), и бросается `TokenValidException`.
4. Все репозитории используют один общий `RequestService`, поэтому новый токен сразу используется во всех следующих запросах, в том числе в долгоживущих процессах: воркерах очередей и Octane.

| Хранилище | Где лежит токен |
|---|---|
| `file` | `storage/app/avito_module/{TOKEN_FILE_NAME}.json`, ключ — хеш ваших учётных данных |
| `database` | Таблица `avito_module_clients` (учётные данные хранятся в виде хешей) |

Чтобы хранить токен по-своему (Redis, кеш, зашифрованное поле…), реализуйте `Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface` и зарегистрируйте реализацию в сервис-провайдере:

```php
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;

$this->app->singleton(TokenStorageInterface::class, RedisTokenStorage::class);
```

## Обработка ошибок

| Исключение | Когда возникает |
|---|---|
| `Domain\Exceptions\ClientCredentialsException` | Не заданы `AVITO_CLIENT_ID` или `AVITO_SECRET` |
| `Domain\Exceptions\TokenValidException` | Токен отклонён (`401`/`403`) даже после обновления; наследует `ClientResponseException` |
| `Domain\Exceptions\TooManyRequestsException` | Превышен лимит запросов (`429`), и запрос не повторялся (POST/PUT, повторы закончились или `Retry-After` дольше `retry.max_wait`); наследует `ClientResponseException` |
| `Domain\Exceptions\ClientResponseException` | Любая другая ошибка API (4xx/5xx или поле `error` в ответе); в сообщении — исходное тело ответа |

В каждом `ClientResponseException` есть разобранные детали ошибки:

| Свойство | Значение |
|---|---|
| `$e->status` | HTTP-статус (то же возвращает `$e->getCode()`) |
| `$e->errorCode` | Код ошибки Авито из тела ответа, например `1009` (не хватает денег) у `applyVas()` |
| `$e->errorMessage` | Текст ошибки Авито из тела ответа |
| `$e->retryAfter` | Секунды из заголовка `Retry-After`, если он есть |

```php
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;
use Voyanara\LaravelApiClient\Domain\Exceptions\TooManyRequestsException;

try {
    $client->messenger()->sendMessage($userId, $chatId, 'Здравствуйте!');
} catch (TooManyRequestsException $e) {
    $this->release($e->retryAfter ?? 60); // внутри задачи в очереди
} catch (ClientResponseException $e) {
    report($e);
}
```

> [!WARNING]
> Не повторяйте вслепую POST/PUT-вызовы вроде `sendMessage()` или `applyVas()`: ошибка не всегда значит, что запрос не выполнился. Авито советует перед повторной покупкой услуги проверить, что её нет в списке применённых.

## Тесты

Тесты разделены на два набора:

| Набор | Папка | Что проверяет | Нужны ключи |
|---|---|---|---|
| `Unit` (по умолчанию) | `tests/Unit` | Код пакета: запросы, разбор ответов, работу с токеном, ошибки. API Авито подменяется через `Http::fake()` | Нет |
| `Live` | `tests/Application` | Что настоящий API Авито по-прежнему отвечает так, как ожидает пакет | Да |

```bash
composer install
composer test        # офлайн-тесты, их же запускает CI
composer test:live   # живые тесты с настоящим API Авито
```

Живым тестам нужны `AVITO_CLIENT_ID` и `AVITO_SECRET`, а части из них ещё `DEMO_SENDER`, `DEMO_CHAT_ID`, `DEMO_VOICE_ID` и `DEMO_BLACKLIST_USER_ID` (см. `.env.testing.example`). Если нужные переменные не заданы, тест пропускается, а не падает. Учтите, что эти тесты отправляют и удаляют настоящие сообщения в чате `DEMO_CHAT_ID`.

Статический анализ и стиль кода:

```bash
composer analyse   # PHPStan
composer lint      # Pint (только проверка; исправить: ./vendor/bin/pint)
```

GitHub Actions прогоняет Pint, PHPStan и офлайн-тесты на PHP 8.3 / 8.4 / 8.5 × Laravel 12 / 13 при каждом push и pull request.

## Дорожная карта

- [x] [Авторизация](https://www.avito.ru/developers/api-catalog/auth/documentation)
- [x] [Информация о пользователе](https://www.avito.ru/developers/api-catalog/user/documentation)
- [x] [Мессенджер](https://www.avito.ru/developers/api-catalog/messenger/documentation)
- [x] [Объявления](https://www.avito.ru/developers/api-catalog/item/documentation)
- [ ] [Рейтинги и отзывы](https://www.avito.ru/developers/api-catalog/ratings/documentation)
- [ ] [Автозагрузка](https://www.avito.ru/developers/api-catalog/autoload/documentation)

## История изменений

Что менялось в каждой версии — в файле [CHANGELOG.md](CHANGELOG.md).

## Как помочь проекту

Pull request'ы приветствуются:

1. Сделайте форк и создайте ветку от `main`.
2. Добавьте или обновите тесты для своего изменения.
3. Перед отправкой запустите `composer test`, `composer lint` и `composer analyse`.
4. Опишите изменение в разделе `Unreleased` файла [CHANGELOG.md](CHANGELOG.md).

Нашли ошибку или нужен метод, которого нет? [Создайте issue](https://github.com/Voyanara/avito-laravel-client/issues).

Если пакет сэкономил вам время, поставьте ⭐: так его проще найти другим разработчикам.

## Лицензия

The MIT License (MIT). Подробнее — в файле [LICENSE](LICENSE).
