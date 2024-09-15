<?php

namespace Voyanara\LaravelApiClient\Infrastructure\Persistence\Storage;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Storage;
use Voyanara\LaravelApiClient\Domain\Interfaces\TokenStorageInterface;
use Voyanara\LaravelApiClient\Presentation\Responses\TokenResponse;
use Override;

readonly class FileStorage implements TokenStorageInterface
{
    #[Override]
    public function get(string $clientId, string $clientSecret): ?string
    {
        $filePath = 'avito_module/'.config('avito.file_name').'.json';

        try {
            $data = Storage::disk('local')->get($filePath);

            if ($data === null) {
                return null;
            }

            $tokens = json_decode($data, true);

            if (! is_array($tokens)) {
                return null;
            }

            $key = md5($clientId).'::'.md5($clientSecret);

            return $tokens[$key] ?? null;
        } catch (FileNotFoundException) {
            return null;
        }
    }

    #[Override]
    public function set(TokenResponse $tokenResponse, string $clientId, string $clientSecret): void
    {
        $filePath = 'avito_module/'.config('avito.file_name').'.json';

        try {
            $data = Storage::disk('local')->get($filePath);
            $tokens = json_decode((string) $data, true);
        } catch (FileNotFoundException) {

            $tokens = [];
        }
        $key = md5($clientId).'::'.md5($clientSecret);
        $tokens[$key] = $tokenResponse->accessToken;
        $json = json_encode($tokens);
        Storage::disk('local')->put($filePath, $json);
    }
}
