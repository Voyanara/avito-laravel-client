<?php

namespace Voyanara\LaravelApiClient\Application\DTO\Items;

use Spatie\LaravelData\Data;

class CallsStatsDayDTO extends Data
{
    /**
     * @param  string  $date  Дата (YYYY-MM-DD)
     * @param  int  $calls  Звонки всего
     * @param  int  $answered  Отвеченные звонки
     * @param  int  $new  Новые звонки
     * @param  int  $newAnswered  Новые и одновременно отвеченные звонки
     */
    public function __construct(
        public readonly string $date,
        public readonly int $calls = 0,
        public readonly int $answered = 0,
        public readonly int $new = 0,
        public readonly int $newAnswered = 0,
    ) {}
}
