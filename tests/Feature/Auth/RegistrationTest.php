<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Fortify\Features;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->skipUnlessFortifyHas(Features::registration());
    }

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'contact_number' => '0917 555 0123',
            'address' => 'Pasig City',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('users', [
            'username' => 'testuser',
            'role' => 'borrower',
        ]);
        $this->assertDatabaseHas('members', [
            'name' => 'Test User',
            'contact_number' => '0917 555 0123',
            'address' => 'Pasig City',
        ]);
    }

    public function test_registration_ignores_a_submitted_role()
    {
        $this->post(route('register.store'), [
            'name' => 'Sneaky User',
            'username' => 'sneaky',
            'email' => 'sneaky@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'contact_number' => '0917 555 0188',
            'address' => 'Sampaloc, Manila',
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('users', [
            'username' => 'sneaky',
            'role' => 'borrower',
        ]);
    }

    public function test_registration_needs_contact_number_and_address()
    {
        $this->post(route('register.store'), [
            'name' => 'Incomplete User',
            'username' => 'incomplete',
            'email' => 'incomplete@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->assertSessionHasErrors(['contact_number', 'address']);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['username' => 'incomplete']);
        $this->assertDatabaseCount('members', 0);
    }
}
