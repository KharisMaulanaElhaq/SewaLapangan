<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndRoleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test penyewa register is immediately active and receives token.
     */
    public function test_penyewa_registration_is_active(): void
    {
        $payload = [
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@test.com',
            'phone' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'penyewa',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(201);
        $response->assertHeader('Location', '/api/v1/me');
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'user' => [
                    'role' => 'penyewa',
                    'status' => 'active',
                ],
                'is_pending_approval' => false,
            ],
        ]);
        $this->assertNotNull($response->json('data.token'));
    }

    /**
     * Test pengelola register starts in pending status (FR-03).
     */
    public function test_pengelola_registration_starts_as_pending(): void
    {
        $payload = [
            'name' => 'Mitra Venue Jaya',
            'email' => 'mitra.venue@test.com',
            'phone' => '081299988877',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'pengelola',
        ];

        $response = $this->postJson('/api/v1/auth/register', $payload);

        $response->assertStatus(201);
        $response->assertHeader('Location', '/api/v1/me');
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'user' => [
                    'role' => 'pengelola',
                    'status' => 'pending',
                ],
                'is_pending_approval' => true,
            ],
        ]);
        // Pending pengelola should not receive a valid login token before approval
        $this->assertNull($response->json('data.token'));
    }

    /**
     * Test logout returns 204 No Content.
     */
    public function test_user_can_logout_with_204_no_content(): void
    {
        $user = User::create([
            'name' => 'User Logout',
            'email' => 'logout@test.com',
            'phone' => '081234567800',
            'password' => bcrypt('password123'),
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(204);
        $this->assertEmpty($response->getContent());
    }

    /**
     * Test authenticated user can update profile (name, phone, and password).
     */
    public function test_user_can_update_profile(): void
    {
        $user = User::create([
            'name' => 'Nama Lama',
            'email' => 'profile@test.com',
            'phone' => '0811223344',
            'password' => bcrypt('oldpassword123'),
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/me', [
                'name' => 'Nama Baru',
                'phone' => '089988776655',
                'password' => 'newpassword123',
                'password_confirmation' => 'newpassword123',
            ]);

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'success',
            'data' => [
                'name' => 'Nama Baru',
                'phone' => '089988776655',
            ],
        ]);

        $freshUser = $user->fresh();
        $this->assertEquals('Nama Baru', $freshUser->name);
        $this->assertEquals('089988776655', $freshUser->phone);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword123', $freshUser->password));
    }

    /**
     * Test validation on profile update.
     */
    public function test_profile_update_validation(): void
    {
        $user = User::create([
            'name' => 'User Validasi',
            'email' => 'validasi@test.com',
            'phone' => '0811223344',
            'password' => bcrypt('password123'),
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        // Missing name and short password
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->putJson('/api/v1/me', [
                'name' => '',
                'phone' => '08123',
                'password' => 'short',
                'password_confirmation' => 'mismatch',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['name', 'password']);
    }

    /**
     * Test admin can verify and activate pending pengelola.
     */
    public function test_admin_can_verify_pending_pengelola(): void
    {
        $admin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@platform.com',
            'phone' => '0811111111',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $pendingPengelola = User::create([
            'name' => 'Calon Pengelola',
            'email' => 'calon@platform.com',
            'phone' => '0822222222',
            'password' => bcrypt('password123'),
            'role' => 'pengelola',
            'status' => 'pending',
        ]);

        // Login as admin
        $adminToken = $admin->createToken('admin-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->patchJson("/api/v1/admin/users/{$pendingPengelola->id}/verify", [
                'status' => 'active',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('active', $pendingPengelola->fresh()->status);
    }

    /**
     * Test admin can search users by query ?q= matching name, email, or phone.
     */
    public function test_admin_can_search_users_by_q(): void
    {
        $admin = User::create([
            'name' => 'Admin Boss',
            'email' => 'admin.search@platform.com',
            'phone' => '081299990001',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Bambang Sudirman',
            'email' => 'bambang@example.com',
            'phone' => '085512345678',
            'password' => bcrypt('password123'),
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        User::create([
            'name' => 'Siti Aminah',
            'email' => 'siti.aminah@venue.com',
            'phone' => '087799881122',
            'password' => bcrypt('password123'),
            'role' => 'pengelola',
            'status' => 'active',
        ]);

        $adminToken = $admin->createToken('admin-token')->plainTextToken;

        // Search by name
        $response = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->getJson('/api/v1/admin/users?q=Bambang');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals('Bambang Sudirman', $response->json('data.0.name'));

        // Search by email
        $responseEmail = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->getJson('/api/v1/admin/users?q=siti.aminah');
        $responseEmail->assertStatus(200);
        $this->assertCount(1, $responseEmail->json('data'));
        $this->assertEquals('Siti Aminah', $responseEmail->json('data.0.name'));

        // Search by phone
        $responsePhone = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->getJson('/api/v1/admin/users?q=085512345678');
        $responsePhone->assertStatus(200);
        $this->assertCount(1, $responsePhone->json('data'));
        $this->assertEquals('Bambang Sudirman', $responsePhone->json('data.0.name'));
    }

    /**
     * Test admin users endpoint whitelist sorting and per_page max 100 limit.
     */
    public function test_admin_users_sorting_whitelist_and_per_page_cap(): void
    {
        $admin = User::create([
            'name' => 'Admin Whitelist',
            'email' => 'admin.wl@platform.com',
            'phone' => '081299990002',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $adminToken = $admin->createToken('admin-token')->plainTextToken;

        // Allowed sort by name ascending
        $responseSortName = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->getJson('/api/v1/admin/users?sort=name');
        $responseSortName->assertStatus(200);
        $responseSortName->assertJsonStructure([
            'status',
            'data',
            'links' => ['first', 'last', 'prev', 'next'],
            'meta' => ['current_page', 'per_page', 'from', 'to', 'total', 'last_page'],
        ]);

        // Disallowed sort should return 422
        $responseInvalidSort = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->getJson('/api/v1/admin/users?sort=unsupported_column');
        $responseInvalidSort->assertStatus(422);

        // per_page > 100 should return 422 validation error
        $responseOverPage = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->getJson('/api/v1/admin/users?per_page=101');
        $responseOverPage->assertStatus(422);

        // per_page exactly 100 should succeed
        $responseValidPage = $this->withHeader('Authorization', 'Bearer ' . $adminToken)
            ->getJson('/api/v1/admin/users?per_page=100');
        $responseValidPage->assertStatus(200);
        $this->assertEquals(100, $responseValidPage->json('meta.per_page'));
    }

    /**
     * Test guest cannot access web profile page.
     */
    public function test_guest_cannot_access_web_profile_page(): void
    {
        $response = $this->get('/profile');
        $response->assertRedirect('/login');
    }

    /**
     * Test authenticated user can access web profile page and update profile.
     */
    public function test_authenticated_user_can_view_and_update_web_profile(): void
    {
        $user = User::create([
            'name' => 'Budi Web Profile',
            'email' => 'budi.web@test.com',
            'phone' => '081234500001',
            'password' => bcrypt('oldpassword123'),
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        // View profile page
        $responseView = $this->actingAs($user)->get('/profile');
        $responseView->assertStatus(200);
        $responseView->assertSee('Pengaturan Profil Pengguna');
        $responseView->assertSee('Budi Web Profile');
        $responseView->assertSee('budi.web@test.com');

        // Update profile (name, phone, password)
        $responseUpdate = $this->actingAs($user)->put('/profile', [
            'name' => 'Budi Web Profile Updated',
            'phone' => '089999990001',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $responseUpdate->assertRedirect();
        $responseUpdate->assertSessionHas('success', 'Profil Anda berhasil diperbarui!');

        $fresh = $user->fresh();
        $this->assertEquals('Budi Web Profile Updated', $fresh->name);
        $this->assertEquals('089999990001', $fresh->phone);
        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('newpassword123', $fresh->password));
    }
}
