<p align="center">
  <img width="70%" src="https://github.com/user-attachments/assets/b794813c-0dd0-4569-9b42-322980ea0968" alt="Avito Laravel Client">
</p>

<p align="center">
  <a href="https://github.com/Voyanara/avito-laravel-client/actions/workflows/tests.yml"><img src="https://img.shields.io/github/actions/workflow/status/Voyanara/avito-laravel-client/tests.yml?branch=main&style=flat-square&label=tests" alt="Tests"></a>
  <a href="https://packagist.org/packages/voyanara/avito-laravel-client"><img src="https://img.shields.io/packagist/v/voyanara/avito-laravel-client.svg?style=flat-square&label=packagist" alt="Latest Version on Packagist"></a>
  <a href="https://packagist.org/packages/voyanara/avito-laravel-client"><img src="https://img.shields.io/packagist/dt/voyanara/avito-laravel-client.svg?style=flat-square" alt="Total Downloads"></a>
  <a href="https://packagist.org/packages/voyanara/avito-laravel-client"><img src="https://img.shields.io/packagist/php-v/voyanara/avito-laravel-client.svg?style=flat-square" alt="PHP Version"></a>
  <img src="https://img.shields.io/badge/Laravel-12%20|%2013-FF2D20?style=flat-square&logo=laravel&logoColor=white" alt="Laravel 12 | 13">
  <a href="LICENSE"><img src="https://img.shields.io/packagist/l/voyanara/avito-laravel-client.svg?style=flat-square" alt="License"></a>
</p>

<p align="center">
  <b>English</b> · <a href="README.ru.md">Русский</a>
</p>

# Avito Laravel Client

A typed Laravel SDK for the [Avito API](https://developers.avito.ru/) (Авито): reply to customers in Avito Messenger, manage webhooks, and read account info, balance and operation history — with automatic OAuth token handling and immutable DTO responses built on [spatie/laravel-data](https://github.com/spatie/laravel-data).

```php
$me = AvitoClient::user()->self();

$chats = AvitoClient::messenger()->getChats($me->id, unreadOnly: true);

foreach ($chats->chats as $chat) {
    AvitoClient::messenger()->sendMessage($me->id, $chat->id, 'Hi! We will get back to you shortly.');
    AvitoClient::messenger()->readChat($me->id, $chat->id);
}
```

## Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
- [API coverage](#api-coverage)
- [Recipes](#recipes)
- [Authentication and token storage](#authentication-and-token-storage)
- [Error handling](#error-handling)
- [Testing](#testing)
- [Roadmap](#roadmap)
- [Changelog](#changelog)
- [Contributing](#contributing)
- [License](#license)

## Features

- **Full Messenger API coverage** — all 13 methods: chats, messages, images, voice messages, blacklist, webhooks.
- **Account API** — profile, balance and operation history.
- **Zero-config auth** — the `client_credentials` token is fetched on first use, cached and refreshed automatically when Avito returns `401`/`403`.
- **Pluggable token storage** — file (default) or database, or your own implementation of `TokenStorageInterface`.
- **Typed responses** — every call returns a readonly DTO with camelCase properties instead of raw arrays.
- **Laravel-native** — auto-discovered service provider, facade, dependency injection, `Http` client under the hood (so `Http::fake()` works in your tests).

## Requirements

| Package version | PHP | Laravel |
|---|---|---|
| `1.3.x` | 8.3 · 8.4 · 8.5 | 12 · 13 |
| `1.2.x` | 8.3 · 8.4 · 8.5 | 11 · 12 · 13 |

> [!NOTE]
> Laravel 11 is no longer supported by the Laravel team, and recent Composer versions refuse to install it because of unpatched security advisories. If you are still on Laravel 11, use `1.2.x`: `composer require voyanara/avito-laravel-client:^1.2`.

You also need an Avito account with API access and a `client_id` / `client_secret` pair from the [Avito developer portal](https://developers.avito.ru/).

## Installation

```bash
composer require voyanara/avito-laravel-client
```

The service provider and the `AvitoClient` facade are registered automatically via package discovery.

The package works out of the box with `.env` variables. Publish the config file only if you want to customize it:

```bash
php artisan vendor:publish --provider="Voyanara\LaravelApiClient\Infrastructure\Providers\AvitoModuleServiceProvider" --tag=config
```

If you want to store the token in the database, also publish and run the migration:

```bash
php artisan vendor:publish --provider="Voyanara\LaravelApiClient\Infrastructure\Providers\AvitoModuleServiceProvider" --tag=migrations
php artisan migrate
```

## Configuration

Add your credentials to `.env`:

```dotenv
AVITO_CLIENT_ID=your-client-id
AVITO_SECRET=your-client-secret

# Optional
AVITO_API_URL=https://api.avito.ru
AVITO_TOKEN_STORAGE=file          # file | database
TOKEN_FILE_NAME=avito-token       # file storage: storage/app/avito_module/avito-token.json
```

| Key | Env variable | Default | Description |
|---|---|---|---|
| `api_url` | `AVITO_API_URL` | `https://api.avito.ru` | Base URL of the Avito API |
| `client_id` | `AVITO_CLIENT_ID` | — | Application client ID |
| `secret` | `AVITO_SECRET` | — | Application client secret |
| `token_storage` | `AVITO_TOKEN_STORAGE` | `file` | Where the access token is cached: `file` or `database` |
| `file_name` | `TOKEN_FILE_NAME` | `avito-token` | File name for the `file` storage (saved to `storage/app/avito_module/{name}.json`) |

## Usage

The entry point is `Voyanara\LaravelApiClient\Application\Facades\Client`, which exposes two groups of methods:

- `->user()` — account API
- `->messenger()` — Messenger API

### Dependency injection (recommended)

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

### Facade

```php
use Voyanara\LaravelApiClient\Application\Facades\AvitoClientFacade as AvitoClient;

$balance = AvitoClient::user()->getBalance($userId);

echo "Real: {$balance->real} ₽, bonus: {$balance->bonus} ₽";
```

> Most Avito endpoints need your Avito user ID. Get it once with `user()->self()->id` and store it, e.g. in config.

## API coverage

### Account — `$client->user()`

| Method | Returns | Description |
|---|---|---|
| `self()` | `UserInfoSelfResponse` | Authorized account: `id`, `name`, `email`, `phone`, `profileUrl` |
| `getBalance(int $userId)` | `UserInfoBalanceResponse` | Wallet balance: `real` and `bonus` |
| `getOperationsHistory(string $dateTimeFrom, string $dateTimeTo)` | `UserInfoOperationsResponse` | Operations for a period (ISO 8601; at most one week, no older than one year) |

### Messenger — `$client->messenger()`

Full coverage of the [Avito Messenger API](https://developers.avito.ru/api-catalog/messenger/documentation):

| Method | Description | Scope |
|---|---|---|
| `getChats(int $userId, int $limit = 10, ?bool $unreadOnly = null, array $itemIds = [], array $chatTypes = [], int $offset = 0)` | List chats; `chatTypes`: `u2i` (about listings), `u2u` (between users) | `messenger:read` |
| `chatInfo(int $userId, string $chatId)` | Chat info with the last message | `messenger:read` |
| `messagesListFromChat(int $userId, string $chatId, int $limit = 10, int $offset = 0)` | Messages in a chat (does not mark it as read) | `messenger:read` |
| `sendMessage(int $userId, string $chatId, string $message, string $type = 'text')` | Send a text message | `messenger:write` |
| `uploadImage(int $userId, string $filePath)` | Upload an image (JPEG, HEIC, GIF, BMP, PNG; up to 24 MB) | `messenger:write` |
| `sendMessageWithImage(int $userId, string $chatId, string $imageId)` | Send a previously uploaded image | `messenger:write` |
| `deleteMessage(int $userId, string $chatId, string $messageId)` | Delete a message (within one hour of sending) | `messenger:write` |
| `readChat(int $userId, string $chatId)` | Mark a chat as read | `messenger:read` |
| `getVoiceFiles(int $userId, array $voiceIds)` | Download links for voice messages (valid for one hour) | `messenger:read` |
| `addUserToBlacklist(int $userId, int $blockedUserId, ?int $itemId = null, ?int $reasonId = null)` | Block a user (reason: `1` spam, `2` fraud, `3` insult, `4` other) | `messenger:write` |
| `subscribeWebhook(string $url)` | Subscribe to webhook notifications (v3) | `messenger:read` |
| `unsubscribeWebhook(string $url)` | Unsubscribe from webhook notifications | `messenger:read` |
| `getSubscriptions()` | List webhook subscriptions | `messenger:read` |

> [!IMPORTANT]
> Since November 2025 Avito requires a paid subscription with Messenger API access to read and send chat messages. Without it the API responds with `402` ("Перейдите на подписку с API мессенджера"). Chat lists, chat info and webhook management remain available.

## Recipes

### Auto-reply to unread chats

```php
$userId = $client->user()->self()->id;

$unread = $client->messenger()->getChats($userId, limit: 50, unreadOnly: true);

foreach ($unread->chats as $chat) {
    $client->messenger()->sendMessage($userId, $chat->id, 'Thanks for your message! A manager will reply within 15 minutes.');
    $client->messenger()->readChat($userId, $chat->id);
}
```

Put this in a scheduled job (`Schedule::job(...)->everyMinute()`) and you have a simple auto-responder.

### Read the conversation history

```php
$history = $client->messenger()->messagesListFromChat($userId, $chatId, limit: 100);

foreach ($history->messages as $message) {
    $who = $message->direction === 'in' ? 'Customer' : 'You';
    echo "{$who}: ".($message->content['text'] ?? "[{$message->type}]").PHP_EOL;
}
```

### Send a photo

```php
$upload = $client->messenger()->uploadImage($userId, storage_path('app/catalog/sofa.jpg'));

$client->messenger()->sendMessageWithImage($userId, $chatId, $upload->id);
```

### Webhooks

```php
$client->messenger()->subscribeWebhook(route('avito.webhook'));

foreach ($client->messenger()->getSubscriptions()->subscriptions as $subscription) {
    echo "{$subscription->url} (v{$subscription->version})".PHP_EOL;
}

$client->messenger()->unsubscribeWebhook(route('avito.webhook'));
```

Avito will send a `POST` request to your URL for every new message, so you can react in real time instead of polling.

### Account balance and spending

```php
$history = $client->user()->getOperationsHistory(
    now()->subWeek()->toIso8601String(),
    now()->toIso8601String(),
);

$spent = $history->operations->sum('amountTotal');
```

## Authentication and token storage

The package uses the OAuth 2.0 `client_credentials` flow:

1. On the first API call the client requests a token from `POST /token` with your `client_id` and `client_secret`. Resolving `Client` from the container makes no HTTP requests.
2. The token is cached in the configured storage, so requests don't fetch a new token every time.
3. If Avito answers `401` or `403`, the token is refreshed once and the request is retried. The new token is reused for all following requests, including long-running processes such as queue workers and Octane.

| Storage | Where the token lives |
|---|---|
| `file` | `storage/app/avito_module/{TOKEN_FILE_NAME}.json`, keyed by a hash of your credentials |
| `database` | `avito_module_clients` table (credentials are stored as hashes) |

To use your own storage (Redis, cache, encrypted column…), implement `Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface` and bind it in a service provider:

```php
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;

$this->app->singleton(TokenStorageInterface::class, RedisTokenStorage::class);
```

## Error handling

| Exception | When |
|---|---|
| `Domain\Exceptions\ClientCredentialsException` | `AVITO_CLIENT_ID` or `AVITO_SECRET` is not set |
| `Domain\Exceptions\TokenValidException` | The token is still rejected (`401`/`403`) after a refresh; extends `ClientResponseException` |
| `Domain\Exceptions\ClientResponseException` | Any other API error (4xx/5xx, or an `error` field in the response); the message contains the raw response body |

```php
use Voyanara\LaravelApiClient\Domain\Exceptions\ClientResponseException;

try {
    $client->messenger()->sendMessage($userId, $chatId, 'Hello!');
} catch (ClientResponseException $e) {
    report($e);
}
```

## Testing

There are two test suites:

| Suite | Folder | What it checks | Needs credentials |
|---|---|---|---|
| `Unit` (default) | `tests/Unit` | The package code: requests, response mapping, token handling, errors. The Avito API is faked with `Http::fake()` | No |
| `Live` | `tests/Application` | That the real Avito API still answers the way the package expects | Yes |

```bash
composer install
composer test        # offline tests, also run in CI
composer test:live   # live tests against the real Avito API
```

The live tests read `AVITO_CLIENT_ID` and `AVITO_SECRET`, and some of them also need `DEMO_SENDER`, `DEMO_CHAT_ID`, `DEMO_VOICE_ID` and `DEMO_BLACKLIST_USER_ID` (see `.env.testing.example`). A live test whose variables are not set is skipped, not failed. Note that they send and delete real messages in `DEMO_CHAT_ID`.

Static analysis and code style:

```bash
composer analyse   # PHPStan
composer lint      # Pint (check only; run ./vendor/bin/pint to fix)
```

GitHub Actions runs Pint, PHPStan and the offline tests on PHP 8.3 / 8.4 / 8.5 × Laravel 12 / 13 for every push and pull request.

## Roadmap

- [x] Authorization (`client_credentials`) with automatic refresh
- [x] Account: profile, balance, operation history
- [x] Messenger: all 13 methods
- [ ] Items: listings, statuses, prices, VAS
- [ ] Autoload: reports and upload status
- [ ] Statistics: views, contacts, favorites
- [ ] Ratings and reviews
- [ ] Webhook payload DTOs and a ready-made controller

Need one of these sooner? Open an issue — or a pull request.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for what has changed in each version.

## Contributing

Pull requests are welcome. Please:

1. Fork the repository and create a branch from `main`.
2. Add or update tests for your change.
3. Run `composer test`, `composer lint` and `composer analyse` before pushing.
4. Describe your change in the `Unreleased` section of [CHANGELOG.md](CHANGELOG.md).

Found a bug or missing endpoint? [Open an issue](https://github.com/Voyanara/avito-laravel-client/issues).

If this package saves you time, please give it a ⭐ — it helps other developers find it.

## License

The MIT License (MIT). See [LICENSE](LICENSE) for details.
