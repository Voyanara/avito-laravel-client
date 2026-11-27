<?php

namespace Voyanara\LaravelApiClient\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use Override;
use Spatie\LaravelData\LaravelDataServiceProvider;
use Voyanara\LaravelApiClient\Infrastructure\Providers\TestingAvitoModuleServiceProvider;

/**
 * Live tests: they call the real Avito API with your credentials.
 * Run them with `./vendor/bin/pest --testsuite=Live`.
 * Without AVITO_CLIENT_ID / AVITO_SECRET every live test is skipped.
 */
class TestCase extends Orchestra
{
    use RefreshDatabase;

    #[Override]
    protected function setUp(): void
    {
        // The SQLite file must exist before RefreshDatabase runs the migrations.
        if (! file_exists(__DIR__.'/database.sqlite')) {
            touch(__DIR__.'/database.sqlite');
        }

        parent::setUp();

        if (! config('avito.client_id') || ! config('avito.secret')) {
            $this->markTestSkipped('Live test: set AVITO_CLIENT_ID and AVITO_SECRET to run it.');
        }

        $testFilePath = base_path('storage/app/avito_module/'.config('avito.file_name').'.json');
        if (file_exists($testFilePath)) {
            unlink($testFilePath);
        }
    }

    /**
     * Skips the test when one of the given environment variables is not set.
     */
    protected function requireEnv(string ...$names): void
    {
        $missing = array_filter($names, fn (string $name): bool => ! env($name));

        if ($missing !== []) {
            $this->markTestSkipped('Live test: set '.implode(', ', $missing).' to run it.');
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
