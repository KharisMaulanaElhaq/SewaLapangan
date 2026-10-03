<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\NotificationLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Send notification when booking is created (pending payment).
     */
    public function notifyBookingCreated(Booking $booking): void
    {
        $user = $booking->user;
        $venue = $booking->court->venue;
        $owner = $venue->owner;

        // Log for customer
        $this->createLog(
            userId: $user->id,
            type: 'booking_created',
            title: "Booking Baru Dibuat: {$booking->code}",
            message: "Booking Anda untuk {$booking->court->name} di {$venue->name} telah dibuat. Silakan lakukan pembayaran DP sebesar Rp " . number_format($booking->dp_amount, 0, ',', '.') . " sebelum " . Carbon::parse($booking->expires_at)->setTimezone('Asia/Jakarta')->format('d M Y H:i') . " WIB."
        );

        // Log for venue owner
        if ($owner) {
            $this->createLog(
                userId: $owner->id,
                type: 'pengelola_new_booking',
                title: "Pemesanan Baru: {$booking->code}",
                message: "Pelanggan {$user->name} membuat pemesanan di lapangan {$booking->court->name}. Menunggu pembayaran DP."
            );
        }
    }

    /**
     * Send notification when payment settlement is received.
     */
    public function notifyPaymentSuccess(Booking $booking): void
    {
        $user = $booking->user;
        $venue = $booking->court->venue;
        $owner = $venue->owner;

        // Customer
        $this->createLog(
            userId: $user->id,
            type: 'payment_success',
            title: "Pembayaran Berhasil! Booking Terkonfirmasi: {$booking->code}",
            message: "Pembayaran DP sebesar Rp " . number_format($booking->dp_amount, 0, ',', '.') . " telah kami terima. Jadwal lapangan Anda telah terkonfirmasi."
        );

        // Pengelola
        if ($owner) {
            $this->createLog(
                userId: $owner->id,
                type: 'payment_settlement_pengelola',
                title: "DP Diterima: {$booking->code}",
                message: "Pembayaran DP Rp " . number_format($booking->dp_amount, 0, ',', '.') . " untuk pemesanan {$booking->code} ({$booking->court->name}) telah lunas/settled."
            );
        }
    }

    /**
     * Send notification when booking expired without payment.
     */
    public function notifyBookingExpired(Booking $booking): void
    {
        $this->createLog(
            userId: $booking->user_id,
            type: 'booking_expired',
            title: "Booking Kedaluwarsa: {$booking->code}",
            message: "Batas waktu pembayaran DP untuk booking {$booking->code} telah habis. Slot telah dilepaskan kembali ke publik."
        );
    }

    /**
     * Send notification when booking is cancelled.
     */
    public function notifyBookingCancelled(Booking $booking): void
    {
        $msg = "Booking {$booking->code} telah dibatalkan.";
        if ($booking->refund_amount > 0) {
            $msg .= " Pengembalian dana (refund) sebesar Rp " . number_format($booking->refund_amount, 0, ',', '.') . " diproses sesuai kebijakan venue.";
        } else {
            $msg .= " Tidak ada pengembalian dana berdasarkan ketentuan batas waktu pembatalan.";
        }

        $this->createLog(
            userId: $booking->user_id,
            type: 'booking_cancelled',
            title: "Booking Dibatalkan: {$booking->code}",
            message: $msg
        );
    }

    /**
     * Store record in notifications_log and log to system logger.
     */
    protected function createLog(int $userId, string $type, string $title, string $message, string $channel = 'email'): NotificationLog
    {
        Log::info("[Notification] [{$channel}] [User {$userId}] {$title}: {$message}");

        return NotificationLog::create([
            'user_id' => $userId,
            'channel' => $channel,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'status' => 'sent',
            'sent_at' => Carbon::now(),
        ]);
    }
}
