<?php

namespace Voyanara\LaravelApiClient\Tests\Application\Actions\Messenger;

use Voyanara\LaravelApiClient\Application\Facades\Client;
use Voyanara\LaravelApiClient\Tests\TestCase;

class AddUserToBlacklistActionTest extends TestCase
{
    public function test_handle(): void
    {
        if (! env('DEMO_BLACKLIST_USER_ID')) {
            $this->markTestSkipped('DEMO_BLACKLIST_USER_ID is not set: blocking is irreversible via API, a dedicated test user id is required.');
        }

        $client = $this->app->make(Client::class);

        $result = $client->messenger()->addUserToBlacklist(
            env('DEMO_SENDER'),
            (int) env('DEMO_BLACKLIST_USER_ID'),
            reasonId: 4
        );

        $this->assertTrue($result);
    }
}
