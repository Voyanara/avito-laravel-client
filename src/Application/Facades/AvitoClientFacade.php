<?php

namespace Voyanara\LaravelApiClient\Application\Facades;

use Illuminate\Support\Facades\Facade;

class AvitoClientFacade extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return AvitoClient::class;
    }

}
