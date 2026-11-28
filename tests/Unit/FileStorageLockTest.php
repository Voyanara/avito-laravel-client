<?php

use Illuminate\Support\Facades\Storage;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;
use Voyanara\LaravelApiClient\Presentation\Responses\TokenResponse;

/**
 * Starts a second PHP process that, like another queue worker in the middle of set(),
 * takes an exclusive flock() on the file, waits and then adds its own entry before unlocking.
 *
 * @return array{0: resource, 1: array<int, resource>}
 */
function holdExclusiveLock(string $path, float $seconds): array
{
    $script = sprintf(
        '$h = fopen(%s, "c+"); flock($h, LOCK_EX); echo "locked\n"; fflush(STDOUT); usleep(%d);'
        .' $data = json_decode(stream_get_contents($h) ?: "{}", true) ?: []; $data["other-worker-key"] = "other-worker-token";'
        .' rewind($h); ftruncate($h, 0); fwrite($h, json_encode($data)); fflush($h); flock($h, LOCK_UN); fclose($h);',
        var_export($path, true),
        (int) ($seconds * 1_000_000),
    );

    $process = proc_open([PHP_BINARY, '-r', $script], [1 => ['pipe', 'w'], 2 => ['pipe', 'w']], $pipes);

    expect($process)->toBeResource()
        ->and(trim((string) fgets($pipes[1])))->toBe('locked');

    return [$process, $pipes];
}

function releaseLockProcess(array $lock): void
{
    [$process, $pipes] = $lock;
    fclose($pipes[1]);
    fclose($pipes[2]);
    proc_close($process);
}

it('does not lose an entry written by another process while it waits for the lock', function () {
    $storage = app(TokenStorageInterface::class);
    $storage->set(TokenResponse::from(avitoToken('old-token')), 'other-client', 'other-secret');

    $lock = holdExclusiveLock(Storage::disk('local')->path('avito_module/avito-token.json'), 2.0);

    $startedAt = microtime(true);
    $storage->set(TokenResponse::from(avitoToken('new-token')), 'test-client', 'test-secret');
    $elapsed = microtime(true) - $startedAt;

    releaseLockProcess($lock);

    $entries = json_decode(Storage::disk('local')->get('avito_module/avito-token.json'), true);

    expect($elapsed)->toBeGreaterThanOrEqual(1.5)
        ->and($entries)->toHaveKey('other-worker-key')
        ->and($entries)->toHaveCount(3)
        ->and($storage->get('other-client', 'other-secret'))->toBe('old-token')
        ->and($storage->get('test-client', 'test-secret'))->toBe('new-token');
});

it('creates the token directory when it does not exist', function () {
    Storage::disk('local')->deleteDirectory('avito_module');

    storeToken('fresh-token');

    expect(app(TokenStorageInterface::class)->get('test-client', 'test-secret'))->toBe('fresh-token');
});

it('returns null when the token file does not exist', function () {
    expect(app(TokenStorageInterface::class)->get('test-client', 'test-secret'))->toBeNull();
});
