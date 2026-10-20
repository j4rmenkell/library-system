<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Member;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page()
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));
        $response->assertOk();
    }

    public function test_staff_see_library_wide_numbers()
    {
        Book::factory()->count(2)->create();
        Borrowing::factory()->overdue()->create();
        Borrowing::factory()->create();

        $this->actingAs(User::factory()->librarian()->create())
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->where('staff.stats.total', 4)
                ->where('staff.stats.out', 1)
                ->where('staff.stats.overdue', 1)
                ->has('staff.pending', 1)
                ->has('staff.overdue', 1)
                ->missing('reader'));
    }

    public function test_readers_see_only_their_own_books()
    {
        $member = Member::factory()->withAccount()->create();
        Borrowing::factory()->borrowed()->for($member)->create();
        Borrowing::factory()->returned()->for($member)->create();
        Borrowing::factory()->create();

        $this->actingAs($member->user)
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page
                ->has('reader.loans', 1)
                ->missing('staff'));
    }
}
