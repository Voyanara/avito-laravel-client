<?php

use Illuminate\Support\ServiceProvider;
use Voyanara\LaravelApiClient\Infrastructure\Providers\AvitoModuleServiceProvider;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\ItemsHttpRepository;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\MessengerHttpRepository;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\TokenHttpRepository;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\UserInfoHttpRepository;
use Voyanara\LaravelApiClient\Infrastructure\Service\RequestService;

/*
 * Safety net for refactoring the service provider: these tests must pass before and after the change.
 */

it('registers the repository as a configured singleton sharing one request service', function (string $repository) {
    $instance = app($repository);

    expect($instance)->toBe(app($repository))
        ->and($instance->requestService)->toBe(app(RequestService::class))
        ->and($instance->apiUrl)->toBe('https://api.avito.ru')
        ->and($instance->clientId)->toBe('test-client')
        ->and($instance->secret)->toBe('test-secret');
})->with([
    ItemsHttpRepository::class,
    MessengerHttpRepository::class,
    TokenHttpRepository::class,
    UserInfoHttpRepository::class,
]);

it('publishes the config and the migrations with their tags', function () {
    $config = ServiceProvider::pathsToPublish(AvitoModuleServiceProvider::class, 'config');
    $migrations = ServiceProvider::pathsToPublish(AvitoModuleServiceProvider::class, 'migrations');

    expect($config)->toHaveCount(1)
        ->and(array_values($config)[0])->toBe(config_path('avito.php'))
        ->and(array_key_first($config))->toEndWith('config/avito.php')
        ->and($migrations)->toHaveCount(1)
        ->and(array_values($migrations)[0])->toBe(database_path('migrations'));
});
