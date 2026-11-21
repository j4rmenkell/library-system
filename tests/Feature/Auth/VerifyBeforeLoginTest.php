<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

// Readers sign up, click the emailed link, and only then can log in
class VerifyBeforeLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_registering_sends_a_link_that_works_without_logging_in(): void
    {
        Notification::fake();

        $this->post(route('register.store'), [
            'name' => 'Maria Clara',
            'username' => 'mariaclara',
            'email' => 'maria@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'contact_number' => '0917 555 0142',
            'address' => 'Sampaloc, Manila',
        ]);

        $user = User::firstWhere('email', 'maria@example.com');
        $link = null;
        Notification::assertSentTo($user, VerifyEmail::class, function (VerifyEmail $mail) use ($user, &$link) {
            $link = $mail->toMail($user)->actionUrl;

            return true;
        });

        $this->assertGuest();
        $this->get($link)->assertRedirect(route('login', absolute: false));
        $this->assertTrue($user->fresh()->hasVerifiedEmail());
    }

    public function test_unverified_readers_cannot_log_in_and_get_a_new_link(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
    }

    public function test_repeated_login_attempts_send_at_most_one_email_a_minute(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password']);
        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password']);

        Notification::assertSentToTimes($user, VerifyEmail::class, 1);
    }

    public function test_a_wrong_password_does_not_send_a_link(): void
    {
        Notification::fake();
        $user = User::factory()->unverified()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');

        Notification::assertNothingSent();
    }

    public function test_verified_readers_log_in_normally(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password']);

        $this->assertAuthenticatedAs($user);
    }

    public function test_a_tampered_link_is_rejected(): void
    {
        $user = User::factory()->unverified()->create();

        $link = URL::temporarySignedRoute('verification.confirm', now()->addHour(), [
            'id' => $user->id,
            'hash' => sha1('someone-else@example.com'),
        ]);

        $this->get($link)->assertForbidden();
        $this->get(route('verification.confirm', ['id' => $user->id, 'hash' => sha1($user->email)]))->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
