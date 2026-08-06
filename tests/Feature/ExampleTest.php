<?php

namespace Tests\Feature;

use App\Models\Banned;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/login')
            ->assertOk()
            ->assertSee('Sign in to your account')
            ->assertHeader('X-Content-Type-Options', 'nosniff')
            ->assertHeader('X-Frame-Options', 'DENY')
            ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $this->get('/register')->assertOk()->assertSee('Create your account');
    }

    public function test_a_registered_user_waits_for_admin_approval(): void
    {
        $response = $this->post('/register', [
            'name' => 'Alex Morgan',
            'email' => 'alex@example.com',
            'contact_number' => '0917 123 4567',
            'password' => 'secure123',
            'password_confirmation' => 'secure123',
            'terms' => '1',
        ]);

        $response
            ->assertRedirect('/approval/pending')
            ->assertSessionHas('status', 'Your account has been created and is waiting for administrator approval.')
            ->assertCookie(auth()->guard()->getRecallerName());
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name' => 'Alex Morgan',
            'email' => 'alex@example.com',
            'contact_number' => '0917 123 4567',
            'approved_at' => null,
        ]);

        $this->get('/dashboard')->assertRedirect('/approval/pending');
    }

    public function test_a_user_can_sign_in_and_sign_out(): void
    {
        $user = User::factory()->create(['password' => 'secure123']);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'secure123',
        ]);

        $response
            ->assertRedirect('/dashboard')
            ->assertCookie(auth()->guard()->getRecallerName());

        $rememberCookie = collect($response->headers->getCookies())
            ->first(fn ($cookie): bool => $cookie->getName() === auth()->guard()->getRecallerName());

        $this->assertNotNull($rememberCookie);
        $this->assertGreaterThan(now()->addDays(399)->timestamp, $rememberCookie->getExpiresTime());

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_google_sign_in_explains_when_credentials_are_missing(): void
    {
        config()->set('services.google.client_id');
        config()->set('services.google.client_secret');

        $this->get('/auth/google')
            ->assertRedirect('/login')
            ->assertSessionHasErrors('google');
    }

    public function test_a_new_google_account_adds_a_contact_number_then_waits_for_approval(): void
    {
        config()->set('services.google.client_id', 'client-id');
        config()->set('services.google.client_secret', 'client-secret');
        config()->set('services.google.redirect', 'https://localhost:8002/auth/google/callback');

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'google-token']),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'sub' => 'google-user-123',
                'email' => 'google@example.com',
                'email_verified' => true,
                'name' => 'Google User',
            ]),
        ]);

        $this->withSession(['google_oauth_state' => 'valid-state'])
            ->get('/auth/google/callback?state=valid-state&code=valid-code')
            ->assertRedirect('/profile/contact-number');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'google@example.com',
            'google_id' => 'google-user-123',
            'approved_at' => null,
        ]);

        $this->put('/profile/contact-number', [
            'contact_number' => '+63 917 123 4567',
        ])->assertRedirect('/approval/pending');

        $this->assertDatabaseHas('users', [
            'email' => 'google@example.com',
            'contact_number' => '+63 917 123 4567',
        ]);
    }

    public function test_an_approved_user_without_a_contact_number_cannot_open_the_dashboard(): void
    {
        $user = User::factory()->create(['contact_number' => null]);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect('/profile/contact-number');

        $this->get('/profile/contact-number')
            ->assertOk()
            ->assertSee('Add your contact number');

        $this->put('/profile/contact-number', [
            'contact_number' => '0918-555-0123',
        ])->assertRedirect('/dashboard');

        $this->get('/dashboard')->assertOk();
    }

    public function test_an_admin_can_approve_a_pending_account(): void
    {
        $admin = User::factory()->admin()->create();
        $pendingUser = User::factory()->pendingApproval()->create();

        $this->actingAs($admin)
            ->get('/admin/approvals')
            ->assertOk()
            ->assertSee($pendingUser->name);

        $this->patch("/admin/approvals/{$pendingUser->id}")
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseHas('users', [
            'id' => $pendingUser->id,
            'approved_by' => $admin->id,
        ]);
        $this->assertNotNull($pendingUser->fresh()->approved_at);

        $this->actingAs($pendingUser->fresh())
            ->get('/dashboard')
            ->assertOk();
    }

    public function test_a_non_admin_cannot_access_account_approvals(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/admin/approvals')
            ->assertForbidden();
    }

    public function test_an_authenticated_user_can_create_a_banned_record_with_a_license(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/banned', [
            'fullname' => 'Juan Dela Cruz',
            'address' => '123 Rizal Street, Davao City',
            'license' => UploadedFile::fake()->image('license.png'),
            'description' => 'Documented unpaid rent and property damage.',
        ]);

        $response
            ->assertRedirect('/dashboard')
            ->assertSessionHas('status', 'Banned renter record added successfully.');

        $record = Banned::query()->sole();

        $this->assertSame($user->id, $record->created_by);
        $this->assertSame('Juan Dela Cruz', $record->fullname);
        $this->assertSame('123 Rizal Street, Davao City', $record->address);
        $this->assertSame('NEW', $record->source);
        Storage::disk('public')->assertExists($record->license);
    }

    public function test_an_authenticated_user_can_create_a_banned_record_without_a_license(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/banned', [
            'fullname' => 'Maria Santos',
            'address' => 'General Santos City',
            'description' => 'Repeatedly violated the rental agreement.',
        ]);

        $response
            ->assertRedirect('/dashboard')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('banned', [
            'fullname' => 'Maria Santos',
            'license' => null,
        ]);
    }

    public function test_the_record_owner_can_delete_a_banned_entry_and_its_license(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('licenses/delete-me.png', 'license-image');
        $owner = User::factory()->create();
        $record = Banned::query()->create([
            'fullname' => 'Owner Delete Record',
            'address' => 'Davao City',
            'license' => 'licenses/delete-me.png',
            'description' => 'Test record',
            'created_by' => $owner->id,
            'date_created' => now(),
        ]);

        $this->actingAs($owner)
            ->delete("/banned/{$record->id}")
            ->assertRedirect()
            ->assertSessionHas('status', 'Banned renter record deleted successfully.');

        $this->assertDatabaseMissing('banned', ['id' => $record->id]);
        Storage::disk('public')->assertMissing('licenses/delete-me.png');
    }

    public function test_an_admin_can_delete_another_users_banned_entry(): void
    {
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $record = Banned::query()->create([
            'fullname' => 'Admin Delete Record',
            'address' => 'Davao City',
            'license' => null,
            'description' => 'Test record',
            'created_by' => $owner->id,
            'date_created' => now(),
        ]);

        $this->actingAs($admin)
            ->delete("/banned/{$record->id}")
            ->assertRedirect();

        $this->assertDatabaseMissing('banned', ['id' => $record->id]);
    }

    public function test_a_non_owner_cannot_delete_a_banned_entry(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $record = Banned::query()->create([
            'fullname' => 'Protected Record',
            'address' => 'Davao City',
            'license' => null,
            'description' => 'Test record',
            'created_by' => $owner->id,
            'date_created' => now(),
        ]);

        $this->actingAs($otherUser)
            ->delete("/banned/{$record->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('banned', ['id' => $record->id]);
    }

    public function test_the_record_owner_can_update_a_banned_entry(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('licenses/original.png', 'original-license');
        $owner = User::factory()->create();
        $record = Banned::query()->create([
            'fullname' => 'Original Name',
            'address' => 'Original Address',
            'license' => 'licenses/original.png',
            'description' => 'Original description',
            'created_by' => $owner->id,
            'date_created' => now(),
        ]);

        $this->actingAs($owner)
            ->patch("/banned/{$record->id}", [
                'license' => UploadedFile::fake()->image('updated.png'),
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Banned renter record updated successfully.');

        $record->refresh();
        $this->assertSame('Original Name', $record->fullname);
        $this->assertSame('Original Address', $record->address);
        $this->assertSame('Original description', $record->description);
        $this->assertNotSame('licenses/original.png', $record->license);
        Storage::disk('public')->assertExists($record->license);
        Storage::disk('public')->assertMissing('licenses/original.png');
        $this->assertDatabaseHas('banned_audit_trails', [
            'banned_id' => $record->id,
            'user_id' => $owner->id,
            'action' => 'Replace',
            'field' => 'license',
            'old_value' => 'licenses/original.png',
        ]);
    }

    public function test_the_record_owner_can_add_a_license_image_to_an_empty_record(): void
    {
        Storage::fake('public');
        $owner = User::factory()->create();
        $record = Banned::query()->create([
            'fullname' => 'Empty License Record',
            'address' => 'Original Address',
            'license' => null,
            'description' => 'Original description',
            'created_by' => $owner->id,
            'date_created' => now(),
        ]);

        $this->actingAs($owner)
            ->patch("/banned/{$record->id}", [
                'license' => UploadedFile::fake()->image('added.png'),
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Banned renter record updated successfully.');

        $record->refresh();
        $this->assertNotNull($record->license);
        $this->assertDatabaseHas('banned_audit_trails', [
            'banned_id' => $record->id,
            'user_id' => $owner->id,
            'action' => 'Added',
            'field' => 'license',
            'old_value' => null,
        ]);
    }

    public function test_the_record_owner_can_remove_an_existing_license_image(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('licenses/remove-me.png', 'license-image');
        $owner = User::factory()->create();
        $record = Banned::query()->create([
            'fullname' => 'Remove License Record',
            'address' => 'Original Address',
            'license' => 'licenses/remove-me.png',
            'description' => 'Original description',
            'created_by' => $owner->id,
            'date_created' => now(),
        ]);

        $this->actingAs($owner)
            ->patch("/banned/{$record->id}", [
                'remove_license' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Banned renter record updated successfully.');

        $record->refresh();
        $this->assertNull($record->license);
        Storage::disk('public')->assertMissing('licenses/remove-me.png');
        $this->assertDatabaseHas('banned_audit_trails', [
            'banned_id' => $record->id,
            'user_id' => $owner->id,
            'action' => 'Removed',
            'field' => 'license',
            'old_value' => 'licenses/remove-me.png',
            'new_value' => null,
        ]);
    }

    public function test_an_admin_can_update_another_users_entry_and_replace_its_license(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('licenses/old.png', 'old-license');
        $admin = User::factory()->admin()->create();
        $owner = User::factory()->create();
        $record = Banned::query()->create([
            'fullname' => 'Admin Editable',
            'address' => 'Davao City',
            'license' => 'licenses/old.png',
            'description' => 'Original description',
            'created_by' => $owner->id,
            'date_created' => now(),
        ]);

        $this->actingAs($admin)
            ->patch("/banned/{$record->id}", [
                'license' => UploadedFile::fake()->image('replacement.png'),
            ])
            ->assertRedirect();

        $record->refresh();
        $this->assertSame('Admin Editable', $record->fullname);
        $this->assertSame('Davao City', $record->address);
        $this->assertSame('Original description', $record->description);
        $this->assertNotSame('licenses/old.png', $record->license);
        Storage::disk('public')->assertExists($record->license);
        Storage::disk('public')->assertMissing('licenses/old.png');
        $this->assertDatabaseHas('banned_audit_trails', [
            'banned_id' => $record->id,
            'user_id' => $admin->id,
            'action' => 'Replace',
            'field' => 'license',
            'old_value' => 'licenses/old.png',
        ]);
    }

    public function test_any_authenticated_user_can_remove_a_license_image(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        Storage::fake('public');
        Storage::disk('public')->put('licenses/shared.png', 'shared-license');
        $record = Banned::query()->create([
            'fullname' => 'Protected Update Record',
            'address' => 'Davao City',
            'license' => 'licenses/shared.png',
            'description' => 'Original description',
            'created_by' => $owner->id,
            'date_created' => now(),
        ]);

        $this->actingAs($otherUser)
            ->patch("/banned/{$record->id}", [
                'remove_license' => '1',
            ])
            ->assertRedirect()
            ->assertSessionHas('status', 'Banned renter record updated successfully.');

        $record->refresh();
        $this->assertNull($record->license);
        Storage::disk('public')->assertMissing('licenses/shared.png');
    }

    public function test_the_registry_can_be_filtered_and_is_paginated(): void
    {
        $user = User::factory()->create(['contact_number' => '0917 555 0199']);

        Banned::query()->create([
            'fullname' => 'Matching Renter',
            'address' => 'Davao City',
            'source' => 'NEW',
            'license' => 'licenses/matching.png',
            'description' => 'Searchable incident',
            'created_by' => $user->id,
            'date_created' => now(),
        ]);

        foreach (range(1, 10) as $index) {
            Banned::query()->create([
                'fullname' => "Other Renter {$index}",
                'address' => "Sample Address {$index}",
                'license' => "licenses/other-{$index}.png",
                'description' => 'Different description',
                'created_by' => $user->id,
                'date_created' => now()->subMinutes($index),
            ]);
        }

        $this->actingAs($user)
            ->get('/dashboard?fullname=Matching')
            ->assertOk()
            ->assertSee('Matching Renter')
            ->assertSee('NEW')
            ->assertSee('0917 555 0199')
            ->assertDontSee('Other Renter 1');

        $this->actingAs($user)
            ->get('/dashboard?fullname=Renter')
            ->assertOk()
            ->assertViewHas('records', fn ($records): bool => $records->total() === 11 && $records->count() === 10);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertOk()
            ->assertViewHas('records', fn ($records): bool => $records->total() === 0);
    }
}
