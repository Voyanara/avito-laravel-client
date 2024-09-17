<?php

namespace Voyanara\LaravelApiClient\Application\Facades;

use Override;
use Illuminate\Support\Facades\Facade;

class AvitoClientFacade extends Facade
{
    #[Override]
    protected static function getFacadeAccessor(): string
    {
        return Client::class;
    }
}
