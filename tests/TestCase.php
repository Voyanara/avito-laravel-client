<?php

namespace Voyanara\LaravelApiClient\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Voyanara\LaravelApiClient\Infrastructure\Providers\TestingAvitoModuleServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Override;
use Spatie\LaravelData\LaravelDataServiceProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    #[Override]
    protected function setUp(): void
    {

        parent::setUp();

        $testFilePath = base_path('storage/app/avito_module/'.config('avito.file_name').'.json');
        if (file_exists($testFilePath)) {
            unlink($testFilePath);
        }

        if (! file_exists(__DIR__.'/database.sqlite')) {
            touch(__DIR__.'/database.sqlite');
        }
    }

    #[Override]
    protected function getPackageProviders($app): array
    {
        return [
            TestingAvitoModuleServiceProvider::class,
            LaravelDataServiceProvider::class,
        ];
    }

    #[Override]
    public function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => __DIR__.'/database.sqlite',
            'prefix' => '',
        ]);
    }
}
