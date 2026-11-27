<?php

namespace Voyanara\LaravelApiClient\Domain\Enums\Items;

/**
 * Дополнительная услуга для устаревшего метода применения дополнительных услуг (putItemVas).
 *
 * @link https://support.avito.ru/articles/200026858 Выделение объявления
 * @link https://support.avito.ru/articles/685 XL-объявление
 */
enum VasEnum: string
{
    /** Выделение объявления */
    case HIGHLIGHT = 'highlight';

    /** XL-объявление */
    case XL = 'xl';
}
