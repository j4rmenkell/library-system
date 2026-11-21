<?php

namespace Tests\Feature\Library;

use App\Models\Category;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Length limits, character rules, sanitising and rate limits on every input
class InputHardeningTest extends TestCase
{
    use RefreshDatabase;

    private User $librarian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->librarian = User::factory()->librarian()->create();
    }

    private function walkIn(array $overrides = []): array
    {
        return ['name' => 'Rosa Aquino', 'contact_number' => '0919 555 0134', 'address' => 'Valenzuela City', ...$overrides];
    }

    public function test_names_accept_real_names_and_reject_symbols_or_long_input(): void
    {
        $this->actingAs($this->librarian)->post('/members', $this->walkIn(['name' => "Niño O'Brien-Cruz Jr."]))
            ->assertSessionHasNoErrors();

        $this->actingAs($this->librarian)->post('/members', $this->walkIn(['name' => 'Rosa <script>']))
            ->assertSessionHasErrors('name');
        $this->actingAs($this->librarian)->post('/members', $this->walkIn(['name' => str_repeat('a', 101)]))
            ->assertSessionHasErrors('name');
    }

    public function test_address_cannot_be_longer_than_the_database_column(): void
    {
        $this->actingAs($this->librarian)->post('/members', $this->walkIn(['address' => str_repeat('a', 256)]))
            ->assertSessionHasErrors('address');

        $this->assertSame(0, Member::count());
    }

    public function test_invisible_control_characters_are_stripped(): void
    {
        $this->actingAs($this->librarian)->post('/categories', ['name' => "Norse\u{0000} Myth\u{202E}ology"]);

        $this->assertDatabaseHas('categories', ['name' => 'Norse Mythology']);
    }

    public function test_line_breaks_in_descriptions_are_kept(): void
    {
        $this->actingAs($this->librarian)->post('/categories', ['name' => 'Celtic', 'description' => "Line one\nLine two"]);

        $this->assertSame("Line one\nLine two", Category::firstWhere('name', 'Celtic')->description);
    }

    public function test_bad_filters_in_the_url_are_rejected_not_crashed_on(): void
    {
        $this->actingAs($this->librarian)->get('/books?category=abc')->assertSessionHasErrors('category');
        $this->actingAs($this->librarian)->get('/books?search='.str_repeat('a', 101))->assertSessionHasErrors('search');
        $this->actingAs($this->librarian)->get('/members?search='.str_repeat('a', 101))->assertSessionHasErrors('search');

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get('/logs?from=yesterday')->assertSessionHasErrors('from');
        $this->actingAs($admin)->get('/logs?user=abc')->assertSessionHasErrors('user');
        $this->actingAs($admin)->get('/users?role=superuser')->assertSessionHasErrors('role');
    }

    public function test_ids_in_urls_must_be_numbers(): void
    {
        $this->actingAs($this->librarian)->get('/books/abc/edit')->assertNotFound();
        $this->actingAs($this->librarian)->get('/members/1%20or%201=1')->assertNotFound();
    }

    public function test_saving_is_limited_to_30_a_minute(): void
    {
        for ($i = 1; $i <= 30; $i++) {
            $this->actingAs($this->librarian)->post('/categories', ['name' => "Category {$i}"]);
        }

        $this->actingAs($this->librarian)->post('/categories', ['name' => 'One too many'])->assertStatus(429);
        $this->actingAs($this->librarian)
            ->withHeader('X-Inertia', 'true')
            ->post('/categories', ['name' => 'Still too many'])
            ->assertSessionHas('error');

        $this->assertSame(30, Category::count());
    }

    public function test_sign_ups_are_limited_per_ip(): void
    {
        for ($i = 1; $i <= 10; $i++) {
            $this->post(route('register.store'), ['name' => 'X']);
        }

        $this->post(route('register.store'), ['name' => 'X'])->assertStatus(429);
    }

    public function test_reset_emails_are_limited_per_ip(): void
    {
        for ($i = 1; $i <= 5; $i++) {
            $this->post(route('password.email'), ['email' => "person{$i}@example.com"]);
        }

        $this->post(route('password.email'), ['email' => 'person6@example.com'])->assertStatus(429);
    }

    public function test_an_enormous_password_is_just_a_failed_login(): void
    {
        $user = User::factory()->create();

        $this->post(route('login.store'), ['email' => $user->email, 'password' => str_repeat('a', 10000)])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
