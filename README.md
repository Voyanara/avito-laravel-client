<p align="center">
  <img width="70%" src="https://github.com/user-attachments/assets/b794813c-0dd0-4569-9b42-322980ea0968">
</p>

## Avito Laravel API PHP Client
> This package is a client for the Avito API designed to simplify integration with the Avito platform from your Laravel applications. It provides a set of tools to work with the Avito API, allowing you to automate and streamline tasks related to managing listings, updating information, and obtaining statistics.

## Why Use the API

The Avito API significantly simplifies working on the platform by allowing you to:

- Automate operations and actions you perform on Avito.
- Integrate Avito with your CRM system, ERP system for content management, messaging system for customer communication, or analytics system.
- Run your business more efficiently by enabling automatic data updates and obtaining statistics.

## Features of the Avito API

With this client, you can:

- **Communicate with Customers**: Use ready-made integrations or create your own with the systems you need.
- **Receive Autoload Information**: Get data on autoload success and errors.
- **Update Prices and Availability**: Quickly update price and availability information on Avito from your system.
- **Obtain Statistics**: Analyze the effectiveness of your listings and get detailed statistics.

Specific methods for each category (e.g., Avito Real Estate or Avito Jobs) are available in the [Avito API documentation](https://developers.avito.ru/).

## Requirements

- **PHP**: ^8.3 (8.3 / 8.4 / 8.5)
- **Laravel Framework**: ^11.0 | ^12.0 | ^13.0
- **Spatie Laravel Data**: ^4.11

Ensure that your environment meets these requirements before proceeding with the installation.

## Installation

Laravel Avito API Client can be installed via Composer:

```bash
composer require voyanara/avito-laravel-client
```

The package will automatically register a service provider.

This package comes with a migration and a configuration file. You can publish them using the following Artisan command:


```bash
php artisan vendor:publish --provider="Voyanara\LaravelApiClient\Infrastructure\Providers\AvitoModuleServiceProvider --tag="config"
```

```bash
php artisan vendor:publish --provider="Voyanara\LaravelApiClient\Infrastructure\Providers\AvitoModuleServiceProvider --tag="migrations"
```

This is the default content of the config file that will be published as config/avito.php

### Usage

You have several options for using the Avito API client in your Laravel application:
#### Using the Facade

To use the Avito API client via a facade, you can call methods directly on the AvitoClient facade.

Example:
```php
public function action(AvitoClient $client)
{
    $client::messenger()->getChats();
    //or
    AvitoClient::user()->self();
}
```
If Laravel does not recognize the facade and you have defined it in composer.json under extra > laravel > aliases, make sure to import it in your file:
```php
use Voyanara\LaravelApiClient\Application\Facades\AvitoClientFacade as AvitoClient;
```

#### Using dependency injection
Alternatively, you can use dependency injection to access the Avito API client. This approach is recommended if you prefer to inject dependencies into your methods or constructors.
```php
use Voyanara\LaravelApiClient\Application\Facades\Client;

public function action(Client $client)
{
    $client->messenger()->getChats();
    //or
    $client->user()->getBalance();
    $client->user()->getOperationsHistory();
}
```

## Messenger

Full coverage of the [Avito Messenger API](https://developers.avito.ru/api-catalog/messenger/documentation) — all 13 methods are available via `$client->messenger()`:

| Method | Description | Scope |
|---|---|---|
| `getChats(int $userId, int $limit = 10, ?bool $unreadOnly = null, array $itemIds = [], array $chatTypes = [], int $offset = 0)` | Get a list of chats | `messenger:read` |
| `chatInfo(int $userId, string $chatId)` | Get chat info with the last message | `messenger:read` |
| `messagesListFromChat(int $userId, string $chatId, int $limit = 10, int $offset = 0)` | Get messages from a chat (does not mark it as read) | `messenger:read` |
| `sendMessage(int $userId, string $chatId, string $message, string $type = 'text')` | Send a text message | `messenger:write` |
| `uploadImage(int $userId, string $filePath)` | Upload an image (JPEG, HEIC, GIF, BMP, PNG; max 24 MB) | `messenger:write` |
| `sendMessageWithImage(int $userId, string $chatId, string $imageId)` | Send a message with a previously uploaded image | `messenger:write` |
| `deleteMessage(int $userId, string $chatId, string $messageId)` | Delete a message (within one hour of sending; its type changes to `deleted`) | `messenger:write` |
| `readChat(int $userId, string $chatId)` | Mark a chat as read | `messenger:read` |
| `getVoiceFiles(int $userId, array $voiceIds)` | Get download links for voice messages (links are valid for one hour) | `messenger:read` |
| `addUserToBlacklist(int $userId, int $blockedUserId, ?int $itemId = null, ?int $reasonId = null)` | Add a user to the blacklist (reasons: 1 — spam, 2 — fraud, 3 — insult, 4 — other) | `messenger:write` |
| `subscribeWebhook(string $url)` | Enable webhook notifications (V3) | `messenger:read` |
| `unsubscribeWebhook(string $url)` | Disable webhook notifications | `messenger:read` |
| `getSubscriptions()` | Get the list of webhook subscriptions | `messenger:read` |

### Examples

Reply to the first unread chat and mark it as read:

```php
use Voyanara\LaravelApiClient\Application\Facades\Client;

public function action(Client $client)
{
    $userId = $client->user()->self()->id;

    $chats = $client->messenger()->getChats($userId, unreadOnly: true);
    $chat = $chats->chats->first();

    $client->messenger()->sendMessage($userId, $chat->id, 'Hello! Thanks for reaching out.');
    $client->messenger()->readChat($userId, $chat->id);
}
```

Send a photo to a chat:

```php
$upload = $client->messenger()->uploadImage($userId, storage_path('app/photo.jpg'));
$client->messenger()->sendMessageWithImage($userId, $chatId, $upload->id);
```

Manage webhook notifications:

```php
$client->messenger()->subscribeWebhook('https://example.com/avito/webhook');

$subscriptions = $client->messenger()->getSubscriptions();
foreach ($subscriptions->subscriptions as $subscription) {
    echo $subscription->url.' (v'.$subscription->version.')'.PHP_EOL;
}

$client->messenger()->unsubscribeWebhook('https://example.com/avito/webhook');
```

> **Note:** since November 2025 Avito requires a paid messenger subscription for reading and sending chat messages — without it the API responds with error `402` ("Перейдите на подписку с API мессенджера"). Chat lists, chat info and webhook management remain available without a subscription.
