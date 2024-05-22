<?php

namespace Tests\Feature;

use App\Models\Tour;
use App\Models\Travel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TourListTest extends TestCase
{
    use RefreshDatabase;

    public function test_tours_list_by_travel_slug_returns_correct_tours(): void
    {
        $travel = Travel::factory()->create();
        $tour = Tour::factory()->create(['travel_id' => $travel->id]);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $tour->id]);

    }

    public function test_price_shown_correctly(): void
    {
        $travel = Travel::factory()->create();
        $tour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 123.45,
        ]);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['price' => '123.45']);

    }

    public function test_tours_list_paginated_data_correctly(): void
    {
        $toursperpage = config('app.paginationPerPage.tours');

        $travel = Travel::factory()->create();
        Tour::factory($toursperpage + 1)->create(['travel_id' => $travel->id]);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');

        $response->assertStatus(200);
        $response->assertJsonCount($toursperpage, 'data');
        $response->assertJsonpath('meta.last_page', 2);

    }

    public function test_tours_list_sort_by_price_correctly(): void
    {
        $travel = Travel::factory()->create();
        $cheapErliarTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 100,
            'starting_date' => now(),
            'ending_date' => now()->addDays(1),
        ]);
        $cheapLaterTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 100,
            'starting_date' => now()->addDays(2),
            'ending_date' => now()->addDays(3),
        ]);
        $expansiveTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 200,
        ]);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours?sortBy=price&sortOrder=asc');

        $response->assertStatus(200);
        $response->assertJsonpath('data.0.id', $cheapErliarTour->id);
        $response->assertJsonpath('data.1.id', $cheapLaterTour->id);
        $response->assertJsonpath('data.2.id', $expansiveTour->id);
    }

    public function test_tours_list_sort_by_starting_date_correctly(): void
    {
        $travel = Travel::factory()->create();
        $erliarTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now(),
            'ending_date' => now()->addDays(1),

        ]);
        $laterTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now()->addDays(2),
            'ending_date' => now()->addDays(3),
        ]);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');

        $response->assertStatus(200);
        $response->assertJsonpath('data.0.id', $erliarTour->id);
        $response->assertJsonpath('data.1.id', $laterTour->id);
    }

    public function test_tours_list_filters_by_price_correctly(): void
    {
        $travel = Travel::factory()->create();
        $cheapTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 10000,
        ]);
        $expansiveTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'price' => 20000,
        ]);
        $endpoint = '/api/v1/travels/'.$travel->slug.'/tours';

        $response = $this->get($endpoint.'?priceFrom=100');
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id' => $cheapTour->id]);
        $response->assertJsonFragment(['id' => $expansiveTour->id]);

        $response = $this->get($endpoint.'?priceFrom=150');
        $response->assertJsonCount(1, 'data');
        $response->assertJsonMissing(['id' => $cheapTour->id]);
        $response->assertJsonFragment(['id' => $expansiveTour->id]);

        $response = $this->get($endpoint.'?priceFrom=250');
        $response->assertJsonCount(0, 'data');

        $response = $this->get($endpoint.'?priceTo=200');
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id' => $cheapTour->id]);
        $response->assertJsonFragment(['id' => $expansiveTour->id]);

        $response = $this->get($endpoint.'?priceTo=150');
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $cheapTour->id]);
        $response->assertJsonMissing(['id' => $expansiveTour->id]);

        $response = $this->get($endpoint.'?priceTo=50');
        $response->assertJsonCount(0, 'data');

        $response = $this->get($endpoint.'?priceFrom=150&priceTo=250');
        $response->assertJsonCount(1, 'data');
        $response->assertJsonMissing(['id' => $cheapTour->id]);
        $response->assertJsonFragment(['id' => $expansiveTour->id]);
    }

    public function test_tours_list_by_starting_date_correctly(): void
    {
        $travel = Travel::factory()->create();
        $erliarTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now(),
            'ending_date' => now()->addDays(1),
        ]);
        $laterTour = Tour::factory()->create([
            'travel_id' => $travel->id,
            'starting_date' => now()->addDays(2),
            'ending_date' => now()->addDays(3),
        ]);
        $endpoint = '/api/v1/travels/'.$travel->slug.'/tours';

        $response = $this->get($endpoint.'?dateFrom='.now());
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id' => $erliarTour->id]);
        $response->assertJsonFragment(['id' => $laterTour->id]);

        $response = $this->get($endpoint.'?dateFrom='.now()->addDay());
        $response->assertJsonCount(1, 'data');
        $response->assertJsonMissing(['id' => $erliarTour->id]);
        $response->assertJsonFragment(['id' => $laterTour->id]);

        $response = $this->get($endpoint.'?dateFrom='.now()->addDays(5));
        $response->assertJsonCount(0, 'data');

        $response = $this->get($endpoint.'?dateTo='.now()->addDay());
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $erliarTour->id]);
        $response->assertJsonMissing(['id' => $laterTour->id]);

        $response = $this->get($endpoint.'?dateTo='.now()->addDays(5));
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id' => $erliarTour->id]);
        $response->assertJsonFragment(['id' => $laterTour->id]);

        $response = $this->get($endpoint.'?dateTo='.now()->subDay());
        $response->assertJsonCount(0, 'data');

        $response = $this->get($endpoint.'?dateFrom='.now()->addDay().'&dateTo='.now()->addDays(5));
        $response->assertJsonCount(1, 'data');
        $response->assertJsonMissing(['id' => $erliarTour->id]);
        $response->assertJsonFragment(['id' => $laterTour->id]);

    }

    public function test_tours_list_validate_data_correctly(): void
    {
        $travel = Travel::factory()->create();

        $endpoint = '/api/v1/travels/'.$travel->slug.'/tours';

        $response = $this->get($endpoint.'?dateFrom=abcd');
        $response->assertStatus(422);

        $response = $this->get($endpoint.'?dateTo=abcd');
        $response->assertStatus(422);
    }
}
