<p align="center">
  <img width="70%" src="https://github.com/user-attachments/assets/b794813c-0dd0-4569-9b42-322980ea0968">
</p>

## Avito Laravel API Client
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

- **PHP**: ^8.3
- **Laravel Framework**: ^11.0
- **Spatie Laravel Data**: ^4.9

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

You have some options to use the Avito API client in your Laravel application:
#### Using the Facade

```php
public function action(AvitoClient $client)
{
    $client::messenger()->getChats();
    //or
    AvitoClient::user()->self();
}
```
If laravel dosent recognize the facade with helper and alieases in composer.json add:
```php
use Voyanara\LaravelApiClient\Application\Facades\AvitoClientFacade as AvitoClient;
```

#### Using dependency injection

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