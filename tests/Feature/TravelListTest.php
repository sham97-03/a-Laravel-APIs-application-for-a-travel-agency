<?php

namespace Tests\Feature;

use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TravelListTest extends TestCase
{
    use RefreshDatabase;

    public function test_travels_list_paginated_data_correctly(): void
    {
        Travel::factory(16)->create(['is_public' => true]);
        $response = $this->get('/api/v1/travels');

        $response->assertStatus(200);
        $response->assertJsonCount(15, 'data');
        $response->assertJsonpath('meta.last_page', 2);

    }

    public function test_travels_list_returns_only_public(): void
    {
        $publictravel = Travel::factory()->create(['is_public' => true]);
        $notpublictravel = Travel::factory()->create(['is_public' => false]);

        $response = $this->get('/api/v1/travels');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonpath('data.0.name', $publictravel->name);

    }
}
