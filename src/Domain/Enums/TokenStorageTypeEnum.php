<?php

namespace Voyanara\LaravelApiClient\Domain\Enums;

enum TokenStorageTypeEnum: string
{
    case FILE = 'file';
    case DATABASE = 'database';
}
