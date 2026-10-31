<?php

namespace Tests\Feature;

use App\Models\Court;
use App\Models\User;
use App\Models\Venue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CourtPhotoUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_pengelola_can_store_court_with_multiple_photos(): void
    {
        Storage::fake('public');

        $pengelola = User::create([
            'name' => 'Owner Surya',
            'email' => 'surya@example.com',
            'password' => bcrypt('password123'),
            'role' => 'pengelola',
            'status' => 'active',
        ]);

        $venue = Venue::create([
            'owner_id' => $pengelola->id,
            'name' => 'Surya Sport Hub',
            'slug' => 'surya-sport-hub',
            'address' => 'Jl. Pahlawan No. 45',
            'city' => 'Semarang',
            'phone' => '024-8765432',
            'dp_percent' => 30,
            'cancel_free_hours' => 24,
            'cancel_refund_percent' => 100,
        ]);

        $photo1 = UploadedFile::fake()->image('court_vinyl_1.jpg');
        $photo2 = UploadedFile::fake()->image('court_vinyl_2.png');

        $response = $this->actingAs($pengelola)->post(route('pengelola.court.store'), [
            'venue_id' => $venue->id,
            'name' => 'Lapangan Futsal Interlock Pro',
            'sport_type' => 'futsal',
            'price' => 125000,
            'open_time' => '07:00',
            'close_time' => '23:00',
            'photos' => [$photo1, $photo2],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $court = Court::where('name', 'Lapangan Futsal Interlock Pro')->first();
        $this->assertNotNull($court);
        $this->assertNotNull($court->image_url);
        $this->assertIsArray($court->images);
        $this->assertCount(2, $court->images);

        // Verify stored files in public disk
        $filename1 = basename($court->images[0]);
        $filename2 = basename($court->images[1]);
        Storage::disk('public')->assertExists('courts/' . $filename1);
        Storage::disk('public')->assertExists('courts/' . $filename2);
    }
}

