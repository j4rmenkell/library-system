<?php

namespace Tests\Feature\Library;

use App\Models\Log;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Users and activity logs: admin-only pages
class AdministrationTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->admin()->create();
    }

    private function newUser(array $overrides = []): array
    {
        return [
            'name' => 'Liza Tan',
            'username' => 'lizatan',
            'email' => 'liza@example.com',
            'role' => 'librarian',
            'password' => 'password',
            'password_confirmation' => 'password',
            ...$overrides,
        ];
    }

    public function test_an_admin_can_create_a_librarian(): void
    {
        $this->actingAs($this->admin)->post('/users', $this->newUser())->assertRedirect('/users');

        $this->assertDatabaseHas('users', ['username' => 'lizatan', 'role' => 'librarian']);
        $this->assertDatabaseHas('logs', ['action' => 'Created user Liza Tan as Librarian']);
    }

    public function test_a_reader_created_by_an_admin_gets_a_member_profile(): void
    {
        $this->actingAs($this->admin)->post('/users', $this->newUser(['role' => 'borrower']));

        $this->assertNotNull(User::firstWhere('username', 'lizatan')->member);
    }

    public function test_editing_keeps_the_password_when_left_empty(): void
    {
        $user = User::factory()->librarian()->create();
        $oldHash = $user->password;

        $this->actingAs($this->admin)->put("/users/{$user->id}", [
            'name' => 'New Name',
            'username' => $user->username,
            'email' => $user->email,
            'role' => 'librarian',
            'password' => '',
            'password_confirmation' => '',
        ])->assertSessionHasNoErrors();

        $this->assertSame('New Name', $user->fresh()->name);
        $this->assertSame($oldHash, $user->fresh()->password);
    }

    public function test_an_admin_cannot_change_their_own_role(): void
    {
        $this->actingAs($this->admin)->put("/users/{$this->admin->id}", [
            'name' => $this->admin->name,
            'username' => $this->admin->username,
            'email' => $this->admin->email,
            'role' => 'borrower',
        ])->assertSessionHasErrors('role');

        $this->assertSame('admin', $this->admin->fresh()->role);
    }

    public function test_signing_in_is_logged(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => 'password']);

        $this->assertDatabaseHas('logs', ['user_id' => $user->id, 'action' => 'Signed in']);
    }

    public function test_logs_are_newest_first_and_filter_by_person(): void
    {
        $other = User::factory()->create();
        Log::create(['user_id' => $this->admin->id, 'action' => 'First']);
        Log::create(['user_id' => $other->id, 'action' => 'Second']);

        $this->actingAs($this->admin)->get('/logs')
            ->assertInertia(fn ($page) => $page->where('logs.data.0.action', 'Second')->where('logs.data.0.user.id', $other->id));
        $this->actingAs($this->admin)->get("/logs?user={$this->admin->id}")
            ->assertInertia(fn ($page) => $page->where('logs.total', 1)->where('logs.data.0.action', 'First'));
    }

    public function test_librarians_cannot_open_admin_pages(): void
    {
        $librarian = User::factory()->librarian()->create();

        $this->actingAs($librarian)->get('/users')->assertForbidden();
        $this->actingAs($librarian)->get('/logs')->assertForbidden();
    }
}
