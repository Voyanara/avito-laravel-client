<?php

namespace Voyanara\LaravelApiClient\Domain\Interfaces;

use Voyanara\LaravelApiClient\Presentation\Responses\TokenResponse;

interface TokenStorageInterface
{
    public function set(TokenResponse $tokenResponse, string $clientId, string $clientSecret): void;

    public function get(string $clientId, string $clientSecret): ?string;
}
