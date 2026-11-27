<?php

namespace Voyanara\LaravelApiClient\Tests\Application\Actions\UserInfo;

use Voyanara\LaravelApiClient\Application\Actions\UserInfo\GetUserOperationHistoryAction;
use Voyanara\LaravelApiClient\Presentation\Responses\UserInfo\UserInfoOperationsResponse;
use Voyanara\LaravelApiClient\Tests\TestCase;

class GetUserOperationHistoryActionTest extends TestCase
{
    public function test_handle(): void
    {
        $action = $this->app->make(GetUserOperationHistoryAction::class);

        $this->assertInstanceOf(
            UserInfoOperationsResponse::class, $action->handle(now()->subWeek()->toIso8601String(), now()->toIso8601String())
        );
    }
}
