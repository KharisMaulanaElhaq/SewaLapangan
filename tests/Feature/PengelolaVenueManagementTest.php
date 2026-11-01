<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PengelolaVenueManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createPengelola(string $email = 'pengelola@example.com'): User
    {
        return User::create([
            'name' => 'Budi Pengelola',
            'email' => $email,
            'password' => bcrypt('password123'),
            'role' => 'pengelola',
            'status' => 'active',
        ]);
    }

    public function test_pengelola_can_create_venue_with_photo_upload(): void
    {
        Storage::fake('public');
        $pengelola = $this->createPengelola();

        $photo = UploadedFile::fake()->image('arena_banner.jpg', 1200, 600);

        $response = $this->actingAs($pengelola)->post(route('pengelola.venue.store'), [
            'name' => 'Garuda Sport Arena Jakarta',
            'city' => 'Jakarta Selatan',
            'address' => 'Jl. Fatmawati Raya No. 10',
            'phone' => '081298765432',
            'description' => 'Arena futsal dan badminton modern berstandar internasional.',
            'dp_percent' => 40,
            'cancel_free_hours' => 12,
            'cancel_refund_percent' => 80,
            'photo' => $photo,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $venue = Venue::where('name', 'Garuda Sport Arena Jakarta')->first();
        $this->assertNotNull($venue);
        $this->assertEquals($pengelola->id, $venue->owner_id);
        $this->assertEquals('garuda-sport-arena-jakarta', $venue->slug);
        $this->assertEquals(40, $venue->dp_percent);
        $this->assertEquals(12, $venue->cancel_free_hours);
        $this->assertEquals(80, $venue->cancel_refund_percent);
        $this->assertNotNull($venue->image_url);

        $filename = basename($venue->image_url);
        Storage::disk('public')->assertExists('venues/' . $filename);
    }

    public function test_pengelola_can_update_their_own_venue(): void
    {
        $pengelola = $this->createPengelola();
        $venue = Venue::create([
            'owner_id' => $pengelola->id,
            'name' => 'Old Arena Name',
            'slug' => 'old-arena-name',
            'city' => 'Jakarta Barat',
            'address' => 'Jl. Kebon Jeruk No. 1',
            'phone' => '0811111111',
            'dp_percent' => 30,
            'cancel_free_hours' => 24,
            'cancel_refund_percent' => 100,
        ]);

        $response = $this->actingAs($pengelola)->put(route('pengelola.venue.update', $venue->id), [
            'name' => 'New Premium Arena Name',
            'city' => 'Jakarta Selatan',
            'address' => 'Jl. TB Simatupang No. 99',
            'phone' => '0822222222',
            'description' => 'Updated description with premium court facilities.',
            'dp_percent' => 50,
            'cancel_free_hours' => 48,
            'cancel_refund_percent' => 75,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $venue->refresh();
        $this->assertEquals('New Premium Arena Name', $venue->name);
        $this->assertEquals('Jakarta Selatan', $venue->city);
        $this->assertEquals('0822222222', $venue->phone);
        $this->assertEquals(50, $venue->dp_percent);
        $this->assertEquals(48, $venue->cancel_free_hours);
        $this->assertEquals(75, $venue->cancel_refund_percent);
    }

    public function test_pengelola_cannot_update_another_pengelola_venue(): void
    {
        $owner1 = $this->createPengelola('owner1@example.com');
        $owner2 = $this->createPengelola('owner2@example.com');

        $venue = Venue::create([
            'owner_id' => $owner1->id,
            'name' => 'Owner 1 Arena',
            'slug' => 'owner-1-arena',
            'city' => 'Bandung',
            'address' => 'Jl. Riau No. 5',
            'phone' => '0833333333',
            'dp_percent' => 30,
            'cancel_free_hours' => 24,
            'cancel_refund_percent' => 100,
        ]);

        $response = $this->actingAs($owner2)->put(route('pengelola.venue.update', $venue->id), [
            'name' => 'Hacked Venue Name',
            'city' => 'Bandung',
            'address' => 'Jl. Riau No. 5',
            'phone' => '0833333333',
            'dp_percent' => 30,
            'cancel_free_hours' => 24,
            'cancel_refund_percent' => 100,
        ]);

        $response->assertNotFound();
        $venue->refresh();
        $this->assertEquals('Owner 1 Arena', $venue->name);
    }

    public function test_pengelola_cannot_delete_venue_with_active_bookings(): void
    {
        $pengelola = $this->createPengelola();
        $penyewa = User::create([
            'name' => 'Penyewa Andi',
            'email' => 'andi@example.com',
            'password' => bcrypt('password123'),
            'role' => 'penyewa',
            'status' => 'active',
        ]);

        $venue = Venue::create([
            'owner_id' => $pengelola->id,
            'name' => 'Active Booking Venue',
            'slug' => 'active-booking-venue',
            'city' => 'Tangerang',
            'address' => 'Jl. BSD Grand No. 8',
            'phone' => '0844444444',
            'dp_percent' => 30,
            'cancel_free_hours' => 24,
            'cancel_refund_percent' => 100,
        ]);

        $court = Court::create([
            'venue_id' => $venue->id,
            'name' => 'Lapangan A',
            'sport_type' => 'futsal',
            'is_active' => true,
        ]);

        Booking::create([
            'court_id' => $court->id,
            'user_id' => $penyewa->id,
            'code' => 'BOOK-TEST-ACTIVE',
            'total_price' => 150000,
            'dp_amount' => 45000,
            'status' => Booking::STATUS_CONFIRMED,
            'expires_at' => now()->addMinutes(15),
        ]);

        $response = $this->actingAs($pengelola)->delete(route('pengelola.venue.destroy', $venue->id));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('venues', ['id' => $venue->id]);
    }

    public function test_pengelola_can_delete_venue_without_active_bookings(): void
    {
        $pengelola = $this->createPengelola();
        $venue = Venue::create([
            'owner_id' => $pengelola->id,
            'name' => 'Empty Venue to Delete',
            'slug' => 'empty-venue-to-delete',
            'city' => 'Surabaya',
            'address' => 'Jl. Pemuda No. 12',
            'phone' => '0855555555',
            'dp_percent' => 30,
            'cancel_free_hours' => 24,
            'cancel_refund_percent' => 100,
        ]);

        $response = $this->actingAs($pengelola)->delete(route('pengelola.venue.destroy', $venue->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('venues', ['id' => $venue->id]);
    }

    public function test_venue_validation_rejects_invalid_dp_percentage(): void
    {
        $pengelola = $this->createPengelola();

        $response = $this->actingAs($pengelola)->post(route('pengelola.venue.store'), [
            'name' => 'Invalid DP Arena',
            'city' => 'Jakarta',
            'address' => 'Jl. Sudirman',
            'phone' => '0812345678',
            'dp_percent' => 5, // Invalid, min is 10
            'cancel_free_hours' => 24,
            'cancel_refund_percent' => 100,
        ]);

        $response->assertSessionHasErrors(['dp_percent']);
        $this->assertDatabaseMissing('venues', ['name' => 'Invalid DP Arena']);
    }
}
