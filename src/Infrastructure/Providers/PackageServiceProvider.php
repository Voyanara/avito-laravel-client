<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Providers;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Override;
use Voyanara\LaravelApiClient\Domain\Enums\TokenStorageTypeEnum;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;
use Voyanara\LaravelApiClient\Infrastructure\Persistence\Storage\DatabaseStorage;
use Voyanara\LaravelApiClient\Infrastructure\Persistence\Storage\FileStorage;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\BaseHttpRepository;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\ItemsHttpRepository;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\MessengerHttpRepository;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\TokenHttpRepository;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\Http\UserInfoHttpRepository;
use Voyanara\LaravelApiClient\Infrastructure\Service\RequestService;

abstract class PackageServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../../config/avito.php' => config_path('avito.php'),
        ], 'config');

        $this->publishesMigrations([
            __DIR__.'/../../../database/migrations' => database_path('migrations'),
        ], 'migrations');
    }

    #[Override]
    public function register(): void
    {
        // Merge in register() so the package works without a published config file.
        $this->mergeConfigFrom(
            __DIR__.'/../../../config/avito.php', 'avito'
        );

        $this->app->singleton(TokenStorageInterface::class, function (Application $app) {
            return match (TokenStorageTypeEnum::from(config('avito.token_storage', 'file'))) {
                TokenStorageTypeEnum::FILE => new FileStorage,
                TokenStorageTypeEnum::DATABASE => $app->make(DatabaseStorage::class),
            };
        });

        // One instance for all repositories, so they share the access token:
        // after a refresh in one repository the others use the new token right away.
        $this->app->singleton(RequestService::class);

        $this->registerRepositories();
    }

    /**
     * Repositories are created lazily, on the first use, with the credentials from the config.
     */
    protected function registerRepositories(): void
    {
        foreach ($this->getRepositories() as $repository) {
            $this->app->singleton($repository, static fn (Application $app): BaseHttpRepository => new $repository(
                apiUrl: config('avito.api_url'),
                clientId: config('avito.client_id'),
                secret: config('avito.secret'),
                tokenStorage: $app->make(TokenStorageInterface::class),
                requestService: $app->make(RequestService::class),
            ));
        }
    }

    /**
     * An explicit list instead of scanning the Repositories/Http folder on every application boot.
     * Add a new repository here.
     *
     * @return list<class-string<BaseHttpRepository>>
     */
    protected function getRepositories(): array
    {
        return [
            ItemsHttpRepository::class,
            MessengerHttpRepository::class,
            TokenHttpRepository::class,
            UserInfoHttpRepository::class,
        ];
    }
}
