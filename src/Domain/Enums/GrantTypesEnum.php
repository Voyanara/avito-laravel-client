<?php

namespace Voyanara\LaravelApiClient\Domain\Enums;

enum GrantTypesEnum: string
{
    case CLIENT_CREDENTIALS = 'client_credentials';
    case REFRESH_TOKEN = 'refresh_token';
    case AUTHORIZATION_CODE = 'authorization_code';
}
