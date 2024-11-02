<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Persistence\Storage;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;
use JsonException;
use Override;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;
use Voyanara\LaravelApiClient\Presentation\Responses\TokenResponse;

readonly class FileStorage implements TokenStorageInterface
{
    private string $filePath;
    public function __construct() {
        $this->filePath = 'avito_module/'.config('avito.file_name').'.json';
    }

    #[Override]
    public function get(string $clientId, string $clientSecret): ?string
    {
        try {
            $tokens = json_decode(Storage::disk('local')->get($this->filePath), true, 512, JSON_THROW_ON_ERROR);
            $key = $this->generateKey($clientId, $clientSecret);
            return $tokens[$key] ?? null;
        } catch (FileNotFoundException | JsonException) {
            return null;
        }
    }


    /**
     * @throws JsonException
     */
    #[Override]
    public function set(TokenResponse $tokenResponse, string $clientId, string $clientSecret): void
    {
        try {
            $tokens = json_decode(Storage::disk('local')->get($this->filePath), true, 512, JSON_THROW_ON_ERROR) ?? [];
        } catch (FileNotFoundException| JsonException) {
            $tokens = [];
        }
        $key = $this->generateKey($clientId, $clientSecret);
        $tokens[$key] = $tokenResponse->accessToken;
        Storage::disk('local')->put($this->filePath, json_encode($tokens, JSON_THROW_ON_ERROR));
    }

    private function generateKey(string $clientId, string $clientSecret): string
    {
        return md5($clientId) . '::' . md5($clientSecret);
    }
}
