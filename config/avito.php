<?php

/*
|--------------------------------------------------------------------------
| Avito API Configuration
|--------------------------------------------------------------------------
| This file is for storing the configuration settings for the Avito API.
| You can set the API URL, client ID, secret, token storage method, and
| the file name for storing the token.
*/

return [
    /*
    |--------------------------------------------------------------------------
    | Avito API URL
    |--------------------------------------------------------------------------
    | This is the base URL for the Avito API. You can set it using the
    | environment variable 'AVITO_API_URL'. If not set, it defaults to
    | 'https://api.avito.ru'.
    |
    | Example: 'https://api.avito.ru'
    */
    'api_url' => env('AVITO_API_URL', 'https://api.avito.ru'),
    /*
    |--------------------------------------------------------------------------
    | Avito Client ID
    |--------------------------------------------------------------------------
    | This is the client ID for accessing the Avito API. You can set it using
    | the environment variable 'AVITO_CLIENT_ID'.
    |
    | Example: 'your-client-id'
    */
    'client_id' => env('AVITO_CLIENT_ID'),
    /*
    |--------------------------------------------------------------------------
    | Avito Secret
    |--------------------------------------------------------------------------
    | This is the secret key for accessing the Avito API. You can set it using
    | the environment variable 'AVITO_SECRET'.
    |
    | Example: 'your-secret-key'
    */
    'secret' => env('AVITO_SECRET'),
    /*
     |--------------------------------------------------------------------------
     | Token Storage Method
     |--------------------------------------------------------------------------
     | This setting determines how the token is stored. You can set it using
     | the environment variable 'AVITO_TOKEN_STORAGE'. The default method is
     | 'file'.
     |
     | Supported: 'file', 'database'
     */
    'token_storage' => env('AVITO_TOKEN_STORAGE', 'file'),
    /*
    |--------------------------------------------------------------------------
    | Token File Name
    |--------------------------------------------------------------------------
    | If the token storage method is set to 'file', this setting determines
    | the name of the file where the token will be stored. You can set it
    | using the environment variable 'TOKEN_FILE_NAME'. The default file name
    | is 'no-name-token'.
    |
    | Example: 'avito-token.json'
    */
    'file_name' => env('TOKEN_FILE_NAME', 'no-nanme-token'),
];
