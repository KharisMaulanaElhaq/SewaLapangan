<?php

namespace App\Console\Commands;

use App\Services\BookingService;
use Illuminate\Console\Command;

class ExpireBookingsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'booking:expire';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire pending bookings whose 15-minute payment window has elapsed and release slots';

    /**
     * Execute the console command.
     */
    public function handle(BookingService $bookingService): int
    {
        $this->info('Checking for expired bookings...');
        $count = $bookingService->expireStalePendingBookings();
        $this->info("Expired {$count} stale booking(s) and released held slots.");

        return Command::SUCCESS;
    }
}
