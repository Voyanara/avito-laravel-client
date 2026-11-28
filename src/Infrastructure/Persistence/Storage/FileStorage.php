<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Persistence\Storage;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Override;
use RuntimeException;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;
use Voyanara\LaravelApiClient\Presentation\Responses\TokenResponse;

/**
 * Keeps tokens in a JSON file on the "local" disk, encrypted with the application key.
 * Several processes (queue workers) can share the file: reads take a shared lock,
 * writes take an exclusive lock for the whole read-modify-write.
 */
readonly class FileStorage implements TokenStorageInterface
{
    private string $filePath;

    public function __construct()
    {
        $this->filePath = 'avito_module/'.config('avito.file_name').'.json';
    }

    #[Override]
    public function get(string $clientId, string $clientSecret): ?string
    {
        $path = Storage::disk('local')->path($this->filePath);

        if (! is_file($path)) {
            return null;
        }

        $encrypted = $this->withLock($path, LOCK_SH, fn ($handle) => $this->read($handle))[$this->key($clientId, $clientSecret)] ?? null;

        if (! is_string($encrypted)) {
            return null;
        }

        try {
            return Crypt::decryptString($encrypted);
        } catch (DecryptException) {
            // A plain text token of an older version or a rotated APP_KEY: a new token will be requested.
            return null;
        }
    }

    #[Override]
    public function set(TokenResponse $tokenResponse, string $clientId, string $clientSecret): void
    {
        $disk = Storage::disk('local');
        $disk->makeDirectory(dirname($this->filePath));

        $this->withLock($disk->path($this->filePath), LOCK_EX, function ($handle) use ($tokenResponse, $clientId, $clientSecret): void {
            $tokens = $this->read($handle);
            $tokens[$this->key($clientId, $clientSecret)] = Crypt::encryptString($tokenResponse->accessToken);

            rewind($handle);
            ftruncate($handle, 0);
            fwrite($handle, json_encode($tokens, JSON_THROW_ON_ERROR));
            fflush($handle);
        });
    }

    /**
     * @template T
     *
     * @param  callable(resource): T  $callback
     * @return T
     */
    private function withLock(string $path, int $operation, callable $callback): mixed
    {
        $handle = fopen($path, $operation === LOCK_EX ? 'c+' : 'r');

        if ($handle === false) {
            throw new RuntimeException('Cannot open the Avito token file: '.$path);
        }

        try {
            flock($handle, $operation);

            return $callback($handle);
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /**
     * @param  resource  $handle
     * @return array<string, mixed>
     */
    private function read($handle): array
    {
        $contents = stream_get_contents($handle);

        try {
            $tokens = $contents ? json_decode($contents, true, 512, JSON_THROW_ON_ERROR) : [];
        } catch (JsonException) {
            return [];
        }

        return is_array($tokens) ? $tokens : [];
    }

    private function key(string $clientId, string $clientSecret): string
    {
        return md5($clientId).'::'.md5($clientSecret);
    }
}
