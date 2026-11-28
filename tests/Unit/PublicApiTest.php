<?php

use Voyanara\LaravelApiClient\Application\DTO\Messenger\MessengerChatContextValueDTO;
use Voyanara\LaravelApiClient\Application\DTO\Messenger\MessengerChatItemDTO;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\BaseHttpRepository;

it('exposes the item owner of a chat as camelCase userId only', function () {
    $chat = MessengerChatItemDTO::from(avitoChat());

    expect($chat->context->value->userId)->toBe(42)
        ->and(property_exists(MessengerChatContextValueDTO::class, 'user_id'))->toBeFalse();
});

it('does not let the API URL of a repository be changed after creation', function () {
    expect((new ReflectionProperty(BaseHttpRepository::class, 'apiUrl'))->isReadOnly())->toBeTrue();
});

it('has no unused token property on repositories', function () {
    expect(property_exists(BaseHttpRepository::class, 'token'))->toBeFalse();
});
