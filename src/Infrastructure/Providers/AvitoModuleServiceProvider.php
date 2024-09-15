<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Providers;

use Override;

class AvitoModuleServiceProvider extends PackageServiceProvider
{
    #[Override]
    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../../../config/avito.php' => config_path('avito.php'),
        ], 'config');

        $this->publishesMigrations([
            __DIR__.'/../../../database/migrations' => database_path('migrations'),
        ], 'migrations');
    }
}
