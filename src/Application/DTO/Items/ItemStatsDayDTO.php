<?php

namespace Voyanara\LaravelApiClient\Application\DTO\Items;

use Spatie\LaravelData\Data;

class ItemStatsDayDTO extends Data
{
    /**
     * Счетчик приходит в ответе, только если он был запрошен в поле fields.
     *
     * @param  string  $date  Дата (YYYY-MM-DD), за которую посчитаны счетчики. Для группировок по периодам — дата начала периода
     * @param  int|null  $uniqViews  Уникальные пользователи, просматривавшие объявление
     * @param  int|null  $uniqContacts  Уникальные пользователи, запрашивавшие контакты объявления
     * @param  int|null  $uniqFavorites  Уникальные пользователи, добавившие объявление в избранное
     * @param  int|null  $views  Устарело, используйте uniqViews. Просмотры объявления
     * @param  int|null  $contacts  Устарело, используйте uniqContacts. Запросы контактов объявления
     * @param  int|null  $favorites  Устарело, используйте uniqFavorites. Добавления объявления в избранное
     */
    public function __construct(
        public readonly string $date,
        public readonly ?int $uniqViews = null,
        public readonly ?int $uniqContacts = null,
        public readonly ?int $uniqFavorites = null,
        public readonly ?int $views = null,
        public readonly ?int $contacts = null,
        public readonly ?int $favorites = null,
    ) {}
}
