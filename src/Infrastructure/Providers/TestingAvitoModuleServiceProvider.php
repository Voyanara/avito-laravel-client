<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Providers;

use Override;

class TestingAvitoModuleServiceProvider extends PackageServiceProvider
{
    #[Override]
    public function boot(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../../../config/avito.php', 'avito'
        );

        $this->loadMigrationsFrom([
            __DIR__.'/../../../database/migrations',
        ]);
    }
}
