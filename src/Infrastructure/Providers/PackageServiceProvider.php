<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Providers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Override;
use ReflectionClass;
use ReflectionException;
use Voyanara\LaravelApiClient\Domain\Enums\TokenStorageTypeEnum;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;
use Voyanara\LaravelApiClient\Infrastructure\Persistence\Storage\DatabaseStorage;
use Voyanara\LaravelApiClient\Infrastructure\Persistence\Storage\FileStorage;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\BaseHttpRepository;

abstract class PackageServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../../../config/avito.php' => config_path('avito.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../../../config/avito.php', 'avito'
        );

        $this->publishesMigrations([
            __DIR__.'/../../../database/migrations' => database_path('migrations'),
        ]);

        $this->loadMigrationsFrom([
            __DIR__.'/../../../database/migrations',
        ]);
    }

    #[Override]
    public function register(): void
    {
        $this->app->singleton(TokenStorageInterface::class, function (Application $app) {
            return match (TokenStorageTypeEnum::from(config('avito.token_storage', 'file'))) {
                TokenStorageTypeEnum::FILE => new FileStorage,
                TokenStorageTypeEnum::DATABASE => $app->make(DatabaseStorage::class),
            };
        });

        $this->registerRepositories();
    }

    protected function registerRepositories(): void
    {
        foreach ($this->getRepositories() as $repository) {
            $this->app->singleton($repository, fn ($app) => $this->createRepositoryInstance($repository, $app));
        }
    }

    /**
     * @throws ReflectionException
     * @throws BindingResolutionException
     */
    protected function createRepositoryInstance(string $class, Application $app)
    {
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();
        $parameters = $constructor ? $constructor->getParameters() : [];

        $dependencies = array_map(function ($parameter) use ($app) {
            $type = $parameter->getType();
            if ($type && ! $type->isBuiltin()) {
                return $app->make($type->getName());
            }

            return match ($parameter->getName()) {
                'apiUrl' => config('avito.api_url'),
                'clientId' => config('avito.client_id'),
                'secret' => config('avito.secret'),
                'token' => null,
                default => throw new \Exception('Unknown parameter: '.$parameter->getName()),
            };
        }, $parameters);

        return $reflection->newInstanceArgs($dependencies);
    }

    protected function getRepositories(): array
    {
        return array_filter(
            array_map(
                [$this, 'getClassFromFile'],
                glob(__DIR__.'/../Repositories/Http'.'/*.php')
            ),
            [$this, 'isRepositoryClass']
        );
    }

    protected function getClassFromFile(string $file): ?string
    {
        $content = file_get_contents($file);
        preg_match('/^namespace\s+([^;]+);/m', $content, $matches);
        $namespace = $matches[1];
        $className = pathinfo($file, PATHINFO_FILENAME);

        return $namespace.'\\'.$className;
    }

    protected function isRepositoryClass(string $class): bool
    {
        return class_exists($class) && is_subclass_of($class, BaseHttpRepository::class);
    }
}
