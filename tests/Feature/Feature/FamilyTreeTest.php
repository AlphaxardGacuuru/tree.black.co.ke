<?php

namespace Tests\Feature\Feature;

use App\Models\FamilyTree;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyTreeTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_creates_a_family_tree_for_new_users(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'New User',
            'email' => 'new-user@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));

        $user = User::query()->where('email', 'new-user@example.com')->firstOrFail();

        $this->assertDatabaseHas('family_trees', [
            'name' => 'New User Family Tree',
            'created_by' => $user->id,
        ]);

        $tree = FamilyTree::query()->firstOrFail();

        $this->assertDatabaseHas('family_tree_user', [
            'family_tree_id' => $tree->id,
            'user_id' => $user->id,
            'role' => 'owner',
        ]);
    }

    public function test_registration_from_share_link_adds_member_and_creates_relationships(): void
    {
        $inviter = User::factory()->create(['gender' => 'male']);
        $tree = FamilyTree::factory()->create(['created_by' => $inviter->id]);
        $tree->members()->attach($inviter->id, ['role' => 'owner', 'joined_at' => now()]);

        $shareResponse = $this->actingAs($inviter)->postJson(route('family-relationships.share-link'), [
            'family_tree_id' => $tree->id,
            'relationship_type' => 'father',
        ]);

        $shareResponse->assertCreated();
        $shareUrl = (string) $shareResponse->json('data.share_url');
        $path = (string) parse_url($shareUrl, PHP_URL_PATH);
        $query = (string) parse_url($shareUrl, PHP_URL_QUERY);

        auth()->logout();

        $joinResponse = $this->get($path . '?' . $query);
        $joinResponse->assertRedirect(route('register', absolute: false));

        $registrationResponse = $this->post(route('register'), [
            'name' => 'Joined Member',
            'email' => 'joined-member@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $registrationResponse->assertRedirect(route('dashboard', absolute: false));

        $joinedUser = User::query()->where('email', 'joined-member@example.com')->firstOrFail();

        $this->assertDatabaseHas('family_tree_user', [
            'family_tree_id' => $tree->id,
            'user_id' => $joinedUser->id,
            'role' => 'member',
        ]);

        $this->assertDatabaseHas('family_relationships', [
            'family_tree_id' => $tree->id,
            'user_id' => $inviter->id,
            'related_user_id' => $joinedUser->id,
            'relationship_type' => 'father',
        ]);

        $this->assertDatabaseHas('family_relationships', [
            'family_tree_id' => $tree->id,
            'user_id' => $joinedUser->id,
            'related_user_id' => $inviter->id,
            'relationship_type' => 'child',
        ]);

        $this->assertDatabaseMissing('family_trees', [
            'created_by' => $joinedUser->id,
        ]);
    }

    public function test_manual_relationship_creation_creates_reverse_edge(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $tree = FamilyTree::factory()->create(['created_by' => $firstUser->id]);

        $tree->members()->attach($firstUser->id, ['role' => 'owner', 'joined_at' => now()]);
        $tree->members()->attach($secondUser->id, ['role' => 'member', 'joined_at' => now()]);

        $response = $this->actingAs($firstUser)->postJson(route('family-relationships.store'), [
            'family_tree_id' => $tree->id,
            'related_user_id' => $secondUser->id,
            'relationship_type' => 'sibling',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('family_relationships', [
            'family_tree_id' => $tree->id,
            'user_id' => $firstUser->id,
            'related_user_id' => $secondUser->id,
            'relationship_type' => 'sibling',
        ]);

        $this->assertDatabaseHas('family_relationships', [
            'family_tree_id' => $tree->id,
            'user_id' => $secondUser->id,
            'related_user_id' => $firstUser->id,
            'relationship_type' => 'sibling',
        ]);
    }

    public function test_non_members_cannot_create_relationships_in_a_tree(): void
    {
        $treeMember = User::factory()->create();
        $nonMember = User::factory()->create();
        $otherMember = User::factory()->create();
        $tree = FamilyTree::factory()->create(['created_by' => $treeMember->id]);

        $tree->members()->attach($treeMember->id, ['role' => 'owner', 'joined_at' => now()]);
        $tree->members()->attach($otherMember->id, ['role' => 'member', 'joined_at' => now()]);

        $response = $this->actingAs($nonMember)->postJson(route('family-relationships.store'), [
            'family_tree_id' => $tree->id,
            'related_user_id' => $otherMember->id,
            'relationship_type' => 'cousin',
        ]);

        $response->assertForbidden();
    }

    public function test_users_can_only_have_one_family_tree_membership(): void
    {
        $owner = User::factory()->create();
        $invitee = User::factory()->create();
        $tree = FamilyTree::factory()->create(['created_by' => $owner->id]);

        $tree->members()->attach($owner->id, ['role' => 'owner', 'joined_at' => now()]);
        $tree->members()->attach($invitee->id, ['role' => 'member', 'joined_at' => now()]);

        $this->assertDatabaseCount('family_tree_user', 2);
    }

    public function test_aunt_relationship_reverse_uses_gender_for_niece_or_nephew(): void
    {
        $owner = User::factory()->create(['gender' => 'male']);
        $aunt = User::factory()->create(['gender' => 'female']);
        $tree = FamilyTree::factory()->create(['created_by' => $owner->id]);

        $tree->members()->attach($owner->id, ['role' => 'owner', 'joined_at' => now()]);
        $tree->members()->attach($aunt->id, ['role' => 'member', 'joined_at' => now()]);

        $response = $this->actingAs($owner)->postJson(route('family-relationships.store'), [
            'family_tree_id' => $tree->id,
            'related_user_id' => $aunt->id,
            'relationship_type' => 'aunt',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('family_relationships', [
            'family_tree_id' => $tree->id,
            'user_id' => $owner->id,
            'related_user_id' => $aunt->id,
            'relationship_type' => 'aunt',
        ]);

        $this->assertDatabaseHas('family_relationships', [
            'family_tree_id' => $tree->id,
            'user_id' => $aunt->id,
            'related_user_id' => $owner->id,
            'relationship_type' => 'nephew',
        ]);
    }
}
