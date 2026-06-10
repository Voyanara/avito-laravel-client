<?php

namespace Voyanara\LaravelApiClient\Tests\Application\Actions\Messenger;

use Voyanara\LaravelApiClient\Application\Facades\Client;
use Voyanara\LaravelApiClient\Presentation\Responses\Messenger\VoiceFilesResponse;
use Voyanara\LaravelApiClient\Tests\TestCase;

class GetVoiceFilesActionTest extends TestCase
{
    public function test_handle(): void
    {
        if (! env('DEMO_VOICE_ID')) {
            $this->markTestSkipped('DEMO_VOICE_ID is not set: a real voice_id from a chat message of type voice is required.');
        }

        $client = $this->app->make(Client::class);

        $response = $client->messenger()->getVoiceFiles(env('DEMO_SENDER'), [env('DEMO_VOICE_ID')]);

        $this->assertInstanceOf(VoiceFilesResponse::class, $response);
        $this->assertNotEmpty($response->voicesUrls);
    }
}
