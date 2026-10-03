<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

class DocsController extends Controller
{
    /**
     * Show interactive API documentation.
     */
    public function index(): View
    {
        return view('docs.api');
    }

    /**
     * Export downloadable Postman Collection JSON.
     */
    public function postman(): JsonResponse
    {
        $baseUrl = url('/api/v1');

        $collection = [
            'info' => [
                'name' => 'Web Service Booking Lapangan Olahraga API v1',
                'description' => 'Dokumentasi REST API Web Service Booking Lapangan Olahraga - Kelompok 3 (Universitas Harkat Negeri 2026)',
                'schema' => 'https://schema.getpostman.com/json/collection/v2.1.0/collection.json',
            ],
            'item' => [
                [
                    'name' => 'Authentication',
                    'item' => [
                        [
                            'name' => 'Register (Penyewa / Pengelola)',
                            'request' => [
                                'method' => 'POST',
                                'header' => [['key' => 'Accept', 'value' => 'application/json']],
                                'url' => ['raw' => "{$baseUrl}/auth/register"],
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode([
                                        'name' => 'Budi Penyewa',
                                        'email' => 'budi@example.com',
                                        'phone' => '08123456789',
                                        'password' => 'password123',
                                        'password_confirmation' => 'password123',
                                        'role' => 'penyewa',
                                    ], JSON_PRETTY_PRINT),
                                    'options' => ['raw' => ['language' => 'json']],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Login',
                            'request' => [
                                'method' => 'POST',
                                'header' => [['key' => 'Accept', 'value' => 'application/json']],
                                'url' => ['raw' => "{$baseUrl}/auth/login"],
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode([
                                        'email' => 'arif.penyewa@gmail.com',
                                        'password' => 'password123',
                                    ], JSON_PRETTY_PRINT),
                                    'options' => ['raw' => ['language' => 'json']],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Public Discovery & Availability',
                    'item' => [
                        [
                            'name' => 'Search Venues',
                            'request' => [
                                'method' => 'GET',
                                'url' => ['raw' => "{$baseUrl}/venues?city=Jakarta&sport=futsal"],
                            ],
                        ],
                        [
                            'name' => 'Get Court Slot Availability',
                            'request' => [
                                'method' => 'GET',
                                'url' => ['raw' => "{$baseUrl}/courts/1/availability?date=" . date('Y-m-d')],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Bookings',
                    'item' => [
                        [
                            'name' => 'Create Booking (Atomic Lock & Hold 15 Mins)',
                            'request' => [
                                'method' => 'POST',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => ['raw' => "{$baseUrl}/bookings"],
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode([
                                        'court_id' => 1,
                                        'slots' => [
                                            gmdate('Y-m-d\TH:00:00\Z', strtotime('+2 days 14:00')),
                                            gmdate('Y-m-d\TH:00:00\Z', strtotime('+2 days 15:00')),
                                        ],
                                        'notes' => 'Sparring futsal antar angkatan',
                                    ], JSON_PRETTY_PRINT),
                                    'options' => ['raw' => ['language' => 'json']],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Get Snap Payment Token',
                            'request' => [
                                'method' => 'POST',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => ['raw' => "{$baseUrl}/bookings/1/pay"],
                            ],
                        ],
                        [
                            'name' => 'Cancel Booking (Venue Policy Refund)',
                            'request' => [
                                'method' => 'POST',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => ['raw' => "{$baseUrl}/bookings/1/cancel"],
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode(['reason' => 'Perubahan jadwal tim'], JSON_PRETTY_PRINT),
                                    'options' => ['raw' => ['language' => 'json']],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => 'Payments & Webhook',
                    'item' => [
                        [
                            'name' => 'Midtrans Webhook',
                            'request' => [
                                'method' => 'POST',
                                'url' => ['raw' => "{$baseUrl}/payments/webhook/midtrans"],
                            ],
                        ],
                        [
                            'name' => 'Simulate Sandbox Payment (Direct Test)',
                            'request' => [
                                'method' => 'POST',
                                'url' => ['raw' => "{$baseUrl}/payments/simulate"],
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode([
                                        'order_id' => 'PAY-BK-DEMO-DP',
                                        'transaction_status' => 'settlement',
                                        'payment_type' => 'qris',
                                    ], JSON_PRETTY_PRINT),
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return response()->json($collection, 200, [
            'Content-Disposition' => 'attachment; filename="booking-lapangan-api-v1.postman_collection.json"',
        ]);
    }
}
