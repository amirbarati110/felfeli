<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUsernameLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_log_in_with_username(): void
    {
        $admin = User::factory()->create([
            'username' => 'admin',
            'password' => 'a-test-password',
        ]);

        $this->post(route('admin.login.store'), [
            'login' => 'admin',
            'password' => 'a-test-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_invalid_username_or_password_is_rejected(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => 'a-test-password',
        ]);

        $this->post(route('admin.login.store'), [
            'login' => 'admin',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('login');

        $this->assertGuest();
    }
}
