<?php

namespace Tests\Feature;

use Tests\TestCase;

class DocsFeatureTest extends TestCase
{
    public function test_docs_page_is_accessible(): void
    {
        $response = $this->get(route('docs'));

        $response->assertStatus(200);
        $response->assertSee('Dokumentasi Web Service Booking Lapangan');
        $response->assertSee('REST API SPECIFICATION v1.0');
        $response->assertSee('Postman Collection');
    }

    public function test_postman_collection_export_returns_valid_json(): void
    {
        $response = $this->get(route('docs.postman'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/json');

        $json = $response->json();
        $this->assertArrayHasKey('info', $json);
        $this->assertArrayHasKey('variable', $json);
        $this->assertArrayHasKey('item', $json);
        $this->assertEquals('Web Service Booking Lapangan Olahraga API v1', $json['info']['name']);
        $this->assertGreaterThanOrEqual(5, count($json['item']));

        // Verify that every endpoint has a standard-compliant Postman URL object with raw, protocol, host, and path
        foreach ($json['item'] as $group) {
            foreach ($group['item'] as $endpoint) {
                $url = $endpoint['request']['url'];
                $this->assertIsArray($url, "URL in endpoint {$endpoint['name']} must be an array/object");
                $this->assertArrayHasKey('raw', $url);
                $this->assertNotEmpty($url['raw']);
                $this->assertArrayHasKey('protocol', $url);
                $this->assertArrayHasKey('host', $url);
                $this->assertNotEmpty($url['host']);
                $this->assertArrayHasKey('path', $url);
                $this->assertNotEmpty($url['path']);
            }
        }
    }

    public function test_openapi_yaml_is_accessible(): void
    {
        $response = $this->get(route('docs.openapi'));

        $response->assertStatus(200);
        $response->assertSee('openapi:');
    }
}
