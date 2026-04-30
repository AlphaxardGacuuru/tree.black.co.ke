<?php

namespace Tests\Feature;

use App\Models\FamilyTree;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $response = $this->get(route('dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_users_can_visit_the_dashboard(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('dashboard'));
    }

    public function test_dashboard_shares_the_authenticated_users_main_family_tree_id(): void
    {
        $user = User::factory()->create();
        $familyTree = FamilyTree::factory()->create(['created_by' => $user->id]);
        $familyTree->members()->attach($user->id, ['role' => 'owner', 'joined_at' => now()]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response
            ->assertOk()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('dashboard')
                    ->where('auth.user.mainFamilyTreeId', $familyTree->id)
            );
    }
}
