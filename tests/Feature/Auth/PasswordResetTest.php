<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_password_reset_request_screen_is_disabled(): void
    {
        $this->get('/forgot-password')->assertNotFound();
    }

    public function test_public_password_reset_request_sends_no_notification(): void
    {
        Notification::fake();
        $user = User::factory()->create();
        $this->post('/forgot-password', ['email' => $user->email])->assertNotFound();
        Notification::assertNothingSent();
    }

    public function test_public_password_reset_token_screen_is_disabled(): void
    {
        $this->get('/reset-password/unused-token')->assertNotFound();
    }

    public function test_disabled_reset_endpoint_cannot_change_a_password(): void
    {
        $user = User::factory()->create();
        $originalPassword = $user->password;
        $this->post('/reset-password', [
            'token' => 'unused-token',
            'email' => $user->email,
            'password' => 'ChangedPassword123!',
            'password_confirmation' => 'ChangedPassword123!',
        ])->assertNotFound();
        $this->assertSame($originalPassword, $user->fresh()->password);
    }
}
