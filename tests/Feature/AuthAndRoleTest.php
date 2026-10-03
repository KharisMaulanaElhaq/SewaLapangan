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
}
