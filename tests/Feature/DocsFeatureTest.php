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
        $this->assertArrayHasKey('item', $json);
        $this->assertEquals('Web Service Booking Lapangan Olahraga API v1', $json['info']['name']);
        $this->assertGreaterThanOrEqual(5, count($json['item']));
    }

    public function test_openapi_yaml_is_accessible(): void
    {
        $response = $this->get(route('docs.openapi'));

        $response->assertStatus(200);
        $response->assertSee('openapi:');
    }
}

