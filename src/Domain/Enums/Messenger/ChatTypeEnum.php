<?php

namespace Voyanara\LaravelApiClient\Domain\Enums\Messenger;

/**
 * Тип чата — фильтр chat_types метода получения информации по чатам (getChatsV2).
 */
enum ChatTypeEnum: string
{
    /** Чаты по объявлениям (значение по умолчанию на стороне Авито) */
    case U2I = 'u2i';

    /** Чаты между пользователями */
    case U2U = 'u2u';

    /** Чаты между пользователями и Авито */
    case A2U = 'a2u';
}
