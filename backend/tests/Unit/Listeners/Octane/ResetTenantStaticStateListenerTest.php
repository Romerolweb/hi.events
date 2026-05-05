<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\Octane;

use HiEvents\Listeners\Octane\ResetTenantStaticStateListener;
use HiEvents\Models\User;
use HiEvents\Resources\BaseResource;
use RuntimeException;
use Tests\TestCase;

class ResetTenantStaticStateListenerTest extends TestCase
{
    protected function tearDown(): void
    {
        User::resetCurrentAccountId();
        BaseResource::resetAdditionalData();
        parent::tearDown();
    }

    public function test_handle_clears_user_account_id_and_resource_data(): void
    {
        User::setCurrentAccountId(99);
        BaseResource::collectionWithAdditionalData([], ['x' => 'y']);

        (new ResetTenantStaticStateListener)->handle();

        $resource = new class(null) extends BaseResource {};
        $this->assertNull($resource->getAdditionalDataByKey('x'));

        $this->expectException(RuntimeException::class);
        User::getCurrentAccountId();
    }
}
