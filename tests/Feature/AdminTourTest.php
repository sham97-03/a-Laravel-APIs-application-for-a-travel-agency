<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\Travel;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTourTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_cannot_adding_travel(): void
    {
        $travel = Travel::factory()->create();

        $response = $this->postJson('/api/v1/admin/travels/'.$travel->id.'/tours');

        $response->assertStatus(401);
    }

    public function test_non_admin_user_cannot_adding_travel(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'editor')->value('id'));
        $travel = Travel::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/admin/travels/'.$travel->id.'/tours');

        $response->assertStatus(403);
    }

    public function test_saves_travel_successfuly_with_valid_data(): void
    {
        $this->seed(RoleSeeder::class);
        $user = User::factory()->create();
        $user->roles()->attach(Role::where('name', 'admin')->value('id'));
        $travel = Travel::factory()->create();

        $response = $this->actingAs($user)->postJson('/api/v1/admin/travels/'.$travel->id.'/tours', [
            'name' => 'Tour name',
        ]);

        $response->assertStatus(422);

        $response = $this->actingAs($user)->postJson('/api/v1/admin/travels/'.$travel->id.'/tours', [
            'name' => 'Tour name',
            'starting_date' => now()->toDateString(),
            'ending_date' => now()->addDays()->toDateString(),
            'price' => 99.99,
        ]);
        $response->assertStatus(201);

        $response = $this->get('/api/v1/travels/'.$travel->slug.'/tours');
        $response->assertJsonFragment(['name' => 'Tour name']);

    }
}
