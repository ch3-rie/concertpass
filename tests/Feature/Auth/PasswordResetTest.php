<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * App uses a session-based mock reset flow (see PasswordResetLinkController / NewPasswordController),
 * not Laravel's password broker notifications.
 */
class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_link_request_redirects_to_mock_reset_form(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/forgot-password', ['email' => $user->email]);

        $response->assertRedirect(route('password.reset', ['token' => 'mock-reset-token'], absolute: false));
        $this->assertEquals($user->email, session('mock_password_reset_email'));
    }

    public function test_reset_password_screen_can_be_rendered_after_request(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        $response = $this->get('/reset-password/mock-reset-token');

        $response->assertStatus(200);
    }

    public function test_password_can_be_reset_with_valid_session(): void
    {
        $user = User::factory()->create();

        $this->post('/forgot-password', ['email' => $user->email]);

        $response = $this->post('/reset-password', [
            'email' => $user->email,
            'password' => 'new-password-aa',
            'password_confirmation' => 'new-password-aa',
        ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('login', absolute: false));

        $this->assertTrue(Hash::check('new-password-aa', $user->fresh()->password));
    }
}
