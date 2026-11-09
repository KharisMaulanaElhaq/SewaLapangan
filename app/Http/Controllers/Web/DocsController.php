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
     * Download or view OpenAPI v1 Specification (YAML).
     */
    public function openapi(): Response
    {
        $path = base_path('docs/openapi.yaml');
        if (! file_exists($path)) {
            abort(404, 'Spesifikasi OpenAPI tidak ditemukan.');
        }

        return response(file_get_contents($path), 200, [
            'Content-Type' => 'text/yaml; charset=utf-8',
            'Content-Disposition' => 'inline; filename="openapi-booking-lapangan-v1.yaml"',
        ]);
    }

    /**
     * Build standard-compliant Postman v2.1 URL object with raw, protocol, host, port, path, and query.
     */
    private function buildPostmanUrl(string $urlStr): array
    {
        $parsed = parse_url($urlStr);
        $scheme = $parsed['scheme'] ?? 'http';
        $hostStr = $parsed['host'] ?? '127.0.0.1';
        $host = explode('.', $hostStr);
        $rawPath = $parsed['path'] ?? '';
        $path = array_values(array_filter(explode('/', $rawPath), fn ($p) => $p !== ''));

        $urlObject = [
            'raw' => $urlStr,
            'protocol' => $scheme,
            'host' => $host,
            'path' => $path,
        ];

        if (isset($parsed['port'])) {
            $urlObject['port'] = (string) $parsed['port'];
        }

        if (isset($parsed['query'])) {
            parse_str($parsed['query'], $queryParams);
            $queryItems = [];
            foreach ($queryParams as $k => $v) {
                $queryItems[] = [
                    'key' => (string) $k,
                    'value' => (string) $v,
                ];
            }
            $urlObject['query'] = $queryItems;
        }

        return $urlObject;
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
            'variable' => [
                [
                    'key' => 'baseUrl',
                    'value' => $baseUrl,
                    'type' => 'string',
                ],
                [
                    'key' => 'TOKEN',
                    'value' => '',
                    'type' => 'string',
                ],
                [
                    'key' => 'TOKEN_PENGELOLA',
                    'value' => '',
                    'type' => 'string',
                ],
                [
                    'key' => 'TOKEN_ADMIN',
                    'value' => '',
                    'type' => 'string',
                ],
            ],
            'item' => [
                [
                    'name' => '1. Authentication',
                    'item' => [
                        [
                            'name' => 'Register (Penyewa / Pengelola)',
                            'request' => [
                                'method' => 'POST',
                                'header' => [['key' => 'Accept', 'value' => 'application/json']],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/auth/register"),
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode([
                                        'name' => 'Pelanggan Demo',
                                        'email' => 'pelanggan.demo@example.com',
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
                            'name' => 'Login (Penyewa)',
                            'event' => [
                                [
                                    'listen' => 'test',
                                    'script' => [
                                        'exec' => [
                                            'var jsonData = pm.response.json();',
                                            'if (jsonData && jsonData.data && jsonData.data.token) {',
                                            '    pm.collectionVariables.set("TOKEN", jsonData.data.token);',
                                            '    console.log("Token Penyewa tersimpan di variable {{TOKEN}}");',
                                            '}',
                                        ],
                                        'type' => 'text/javascript',
                                    ],
                                ],
                            ],
                            'request' => [
                                'method' => 'POST',
                                'header' => [['key' => 'Accept', 'value' => 'application/json']],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/auth/login"),
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode([
                                        'email' => 'arif.penyewa@gmail.com',
                                        'password' => 'password123',
                                        'device_name' => 'postman',
                                    ], JSON_PRETTY_PRINT),
                                    'options' => ['raw' => ['language' => 'json']],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Login (Pengelola)',
                            'event' => [
                                [
                                    'listen' => 'test',
                                    'script' => [
                                        'exec' => [
                                            'var jsonData = pm.response.json();',
                                            'if (jsonData && jsonData.data && jsonData.data.token) {',
                                            '    pm.collectionVariables.set("TOKEN", jsonData.data.token);',
                                            '    pm.collectionVariables.set("TOKEN_PENGELOLA", jsonData.data.token);',
                                            '    console.log("Token Pengelola tersimpan di variable {{TOKEN_PENGELOLA}} dan {{TOKEN}}");',
                                            '}',
                                        ],
                                        'type' => 'text/javascript',
                                    ],
                                ],
                            ],
                            'request' => [
                                'method' => 'POST',
                                'header' => [['key' => 'Accept', 'value' => 'application/json']],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/auth/login"),
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode([
                                        'email' => 'pengelola@sportvenue.com',
                                        'password' => 'password123',
                                        'device_name' => 'postman',
                                    ], JSON_PRETTY_PRINT),
                                    'options' => ['raw' => ['language' => 'json']],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Login (Admin)',
                            'event' => [
                                [
                                    'listen' => 'test',
                                    'script' => [
                                        'exec' => [
                                            'var jsonData = pm.response.json();',
                                            'if (jsonData && jsonData.data && jsonData.data.token) {',
                                            '    pm.collectionVariables.set("TOKEN", jsonData.data.token);',
                                            '    pm.collectionVariables.set("TOKEN_ADMIN", jsonData.data.token);',
                                            '    console.log("Token Admin tersimpan di variable {{TOKEN_ADMIN}} dan {{TOKEN}}");',
                                            '}',
                                        ],
                                        'type' => 'text/javascript',
                                    ],
                                ],
                            ],
                            'request' => [
                                'method' => 'POST',
                                'header' => [['key' => 'Accept', 'value' => 'application/json']],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/auth/login"),
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode([
                                        'email' => 'admin@harkat.ac.id',
                                        'password' => 'password123',
                                        'device_name' => 'postman',
                                    ], JSON_PRETTY_PRINT),
                                    'options' => ['raw' => ['language' => 'json']],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Get Profile (Me)',
                            'request' => [
                                'method' => 'GET',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/auth/me"),
                            ],
                        ],
                        [
                            'name' => 'Update Profile',
                            'request' => [
                                'method' => 'PUT',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/auth/me"),
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode([
                                        'name' => 'Pelanggan Demo Updated',
                                        'phone' => '08199999999',
                                    ], JSON_PRETTY_PRINT),
                                    'options' => ['raw' => ['language' => 'json']],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Logout',
                            'request' => [
                                'method' => 'POST',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/auth/logout"),
                            ],
                        ],
                    ],
                ],
                [
                    'name' => '2. Public Discovery & Availability',
                    'item' => [
                        [
                            'name' => 'Search Venues',
                            'request' => [
                                'method' => 'GET',
                                'url' => $this->buildPostmanUrl("{$baseUrl}/venues?city=Jakarta&sport=futsal"),
                            ],
                        ],
                        [
                            'name' => 'Get Venue Detail',
                            'request' => [
                                'method' => 'GET',
                                'url' => $this->buildPostmanUrl("{$baseUrl}/venues/1"),
                            ],
                        ],
                        [
                            'name' => 'Get Court Slot Availability',
                            'request' => [
                                'method' => 'GET',
                                'url' => $this->buildPostmanUrl("{$baseUrl}/courts/1/availability?date=".date('Y-m-d')),
                            ],
                        ],
                    ],
                ],
                [
                    'name' => '3. Bookings',
                    'item' => [
                        [
                            'name' => 'Get My Bookings',
                            'request' => [
                                'method' => 'GET',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/bookings"),
                            ],
                        ],
                        [
                            'name' => 'Get Booking Detail by ID',
                            'request' => [
                                'method' => 'GET',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/bookings/1"),
                            ],
                        ],
                        [
                            'name' => 'Create Booking (Atomic Lock & Hold 15 Mins)',
                            'request' => [
                                'method' => 'POST',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/bookings"),
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
                            'name' => 'Get Snap Payment Token (Midtrans Pay)',
                            'request' => [
                                'method' => 'POST',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/bookings/1/pay"),
                            ],
                        ],
                        [
                            'name' => 'Cancel Booking (Snapshot Policy Refund)',
                            'request' => [
                                'method' => 'POST',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/bookings/1/cancel"),
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
                    'name' => '4. Pengelola Venue Management',
                    'item' => [
                        [
                            'name' => 'List Owner Venues',
                            'request' => [
                                'method' => 'GET',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/manage/venues"),
                            ],
                        ],
                        [
                            'name' => 'Create Venue',
                            'request' => [
                                'method' => 'POST',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/manage/venues"),
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode([
                                        'name' => 'Arena Sport Hub Baru',
                                        'city' => 'Jakarta Selatan',
                                        'address' => 'Jl. RS Fatmawati No. 15',
                                        'phone' => '081299998888',
                                        'dp_percent' => 30,
                                        'cancel_free_hours' => 24,
                                        'cancel_refund_percent' => 100,
                                        'description' => 'Fasilitas futsal vinyl & badminton karpet premium',
                                    ], JSON_PRETTY_PRINT),
                                    'options' => ['raw' => ['language' => 'json']],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Update Venue',
                            'request' => [
                                'method' => 'PUT',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/manage/venues/1"),
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode([
                                        'name' => 'Champions Sport Arena Jakarta',
                                        'city' => 'Jakarta Selatan',
                                        'address' => 'Jl. TB Simatupang No. 88, Cilandak',
                                        'phone' => '021-7890123',
                                        'dp_percent' => 35,
                                        'cancel_free_hours' => 24,
                                        'cancel_refund_percent' => 100,
                                    ], JSON_PRETTY_PRINT),
                                    'options' => ['raw' => ['language' => 'json']],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Create Court',
                            'request' => [
                                'method' => 'POST',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/manage/courts"),
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode([
                                        'venue_id' => 1,
                                        'name' => 'Lapangan Badminton 2',
                                        'sport_type' => 'badminton',
                                        'is_active' => true,
                                        'open_time' => '08:00',
                                        'close_time' => '23:00',
                                        'price' => 75000,
                                    ], JSON_PRETTY_PRINT),
                                    'options' => ['raw' => ['language' => 'json']],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Add Tier Price Rule',
                            'request' => [
                                'method' => 'POST',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/manage/prices"),
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode([
                                        'court_id' => 1,
                                        'day_type' => 'weekend',
                                        'start_time' => '17:00',
                                        'end_time' => '23:00',
                                        'price' => 180000,
                                    ], JSON_PRETTY_PRINT),
                                    'options' => ['raw' => ['language' => 'json']],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Block Court Schedule (Maintenance)',
                            'request' => [
                                'method' => 'POST',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/manage/blocks"),
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode([
                                        'court_id' => 1,
                                        'start_at' => date('Y-m-d 10:00:00'),
                                        'end_at' => date('Y-m-d 14:00:00'),
                                        'reason' => 'Perawatan rumput sintetis',
                                    ], JSON_PRETTY_PRINT),
                                    'options' => ['raw' => ['language' => 'json']],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Update Booking Operational Status (COMPLETED / NO_SHOW)',
                            'request' => [
                                'method' => 'PATCH',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/manage/bookings/1/status"),
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode(['status' => 'COMPLETED'], JSON_PRETTY_PRINT),
                                    'options' => ['raw' => ['language' => 'json']],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Delete Schedule Block',
                            'request' => [
                                'method' => 'DELETE',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/manage/blocks/1"),
                            ],
                        ],
                        [
                            'name' => 'Delete Court Price Rule',
                            'request' => [
                                'method' => 'DELETE',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/manage/prices/1"),
                            ],
                        ],
                        [
                            'name' => 'Delete Court',
                            'request' => [
                                'method' => 'DELETE',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/manage/courts/1"),
                            ],
                        ],
                        [
                            'name' => 'Delete Venue (Soft Guard)',
                            'request' => [
                                'method' => 'DELETE',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/manage/venues/1"),
                            ],
                        ],
                    ],
                ],
                [
                    'name' => '5. Payments & Webhooks',
                    'item' => [
                        [
                            'name' => 'Midtrans Webhook',
                            'request' => [
                                'method' => 'POST',
                                'url' => $this->buildPostmanUrl("{$baseUrl}/payments/webhook/midtrans"),
                            ],
                        ],
                        [
                            'name' => 'Simulate Sandbox Payment (Direct Test)',
                            'request' => [
                                'method' => 'POST',
                                'url' => $this->buildPostmanUrl("{$baseUrl}/payments/simulate"),
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode([
                                        'order_id' => 'PAY-BK-SEED-DP',
                                        'transaction_status' => 'settlement',
                                        'payment_type' => 'qris',
                                    ], JSON_PRETTY_PRINT),
                                    'options' => ['raw' => ['language' => 'json']],
                                ],
                            ],
                        ],
                    ],
                ],
                [
                    'name' => '6. Admin Platform',
                    'item' => [
                        [
                            'name' => 'List Users',
                            'request' => [
                                'method' => 'GET',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/admin/users?role=pengelola"),
                            ],
                        ],
                        [
                            'name' => 'Verify Pengelola',
                            'request' => [
                                'method' => 'PATCH',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/admin/users/3/verify"),
                                'body' => [
                                    'mode' => 'raw',
                                    'raw' => json_encode(['status' => 'active'], JSON_PRETTY_PRINT),
                                    'options' => ['raw' => ['language' => 'json']],
                                ],
                            ],
                        ],
                        [
                            'name' => 'Transactions Summary',
                            'request' => [
                                'method' => 'GET',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/admin/transactions"),
                            ],
                        ],
                        [
                            'name' => 'Webhook Logs',
                            'request' => [
                                'method' => 'GET',
                                'header' => [
                                    ['key' => 'Accept', 'value' => 'application/json'],
                                    ['key' => 'Authorization', 'value' => 'Bearer {{TOKEN}}'],
                                ],
                                'url' => $this->buildPostmanUrl("{$baseUrl}/admin/webhook-logs"),
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
