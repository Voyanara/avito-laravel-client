<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/tests',
    ])
    ->withSkip([
        __DIR__.'/src/Infrastructure/Providers/PackageServiceProvider.php',
    ])
    ->withPhpSets(php83: true)
    ->withTypeCoverageLevel(47)
    ->withPreparedSets(deadCode: true, codeQuality: true)->withImportNames(removeUnusedImports: true);
