<?php

use Voyanara\LaravelApiClient\Application\DTO\Messenger\MessengerChatItemDTO;
use Voyanara\LaravelApiClient\Infrastructure\Repositories\BaseHttpRepository;

it('exposes the item owner of a chat as camelCase userId', function () {
    $chat = MessengerChatItemDTO::from(avitoChat());

    expect($chat->context->value->userId)->toBe(42)
        // Kept for backward compatibility, deprecated.
        ->and($chat->context->value->user_id)->toBe(42);
});

it('does not let repository settings be changed after creation', function (string $property) {
    expect((new ReflectionProperty(BaseHttpRepository::class, $property))->isReadOnly())->toBeTrue();
})->with(['apiUrl', 'token']);
