<?php

declare(strict_types=1);

namespace HiEvents\Listeners\Octane;

use HiEvents\Models\User;
use HiEvents\Resources\BaseResource;

class ResetTenantStaticStateListener
{
    public function handle(): void
    {
        User::resetCurrentAccountId();
        BaseResource::resetAdditionalData();
    }
}
