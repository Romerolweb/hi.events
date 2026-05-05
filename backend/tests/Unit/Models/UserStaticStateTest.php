<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use HiEvents\Models\User;
use RuntimeException;
use Tests\TestCase;

class UserStaticStateTest extends TestCase
{
    protected function tearDown(): void
    {
        User::resetCurrentAccountId();
        parent::tearDown();
    }

    public function test_set_and_get_current_account_id(): void
    {
        User::setCurrentAccountId(42);
        $this->assertSame(42, User::getCurrentAccountId());
    }

    public function test_get_throws_when_not_set(): void
    {
        User::resetCurrentAccountId();

        $this->expectException(RuntimeException::class);
        User::getCurrentAccountId();
    }

    public function test_reset_clears_account_id(): void
    {
        User::setCurrentAccountId(7);
        User::resetCurrentAccountId();

        $this->expectException(RuntimeException::class);
        User::getCurrentAccountId();
    }
}
