<?php

namespace Voyanara\LaravelApiClient\Application\Facades;

use Illuminate\Support\Facades\Facade;
use Override;

class AvitoClientFacade extends Facade
{
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return Client::class;
    }
}
