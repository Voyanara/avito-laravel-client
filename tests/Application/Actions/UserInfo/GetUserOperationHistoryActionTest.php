<?php

namespace Voyanara\LaravelApiClient\Tests\Application\Actions\UserInfo;

use Voyanara\LaravelApiClient\Application\Actions\UserInfo\GetUserOperationHistoryAction;
use Voyanara\LaravelApiClient\Presentation\Responses\UserInfo\UserInfoOperationsResponse;
use Voyanara\LaravelApiClient\Tests\TestCase;

class GetUserOperationHistoryActionTest extends TestCase
{
    public function testHandle(): void
    {
        $action = $this->app->make(GetUserOperationHistoryAction::class);

        $this->assertInstanceOf(
            UserInfoOperationsResponse::class, $action->handle('10/09/2024', '11/09/2024')
        );
    }
}
