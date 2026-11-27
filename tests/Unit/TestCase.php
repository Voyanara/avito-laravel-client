<?php

namespace Voyanara\LaravelApiClient\Tests\Unit;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Orchestra\Testbench\TestCase as Orchestra;
use Override;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Voyanara\LaravelApiClient\Infrastructure\Providers\AvitoModuleServiceProvider;

/**
 * Offline tests: the Avito API is faked with Http::fake(), no credentials or network needed.
 * Uses the real AvitoModuleServiceProvider without a published config file.
 */
abstract class TestCase extends Orchestra
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Http::preventStrayRequests();
    }

    #[Override]
    protected function getPackageProviders($app): array
    {
        return [
            LaravelDataServiceProvider::class,
            AvitoModuleServiceProvider::class,
        ];
    }

    #[Override]
    protected function defineEnvironment($app): void
    {
        $app['config']->set('avito.client_id', 'test-client');
        $app['config']->set('avito.secret', 'test-secret');
    }
}
