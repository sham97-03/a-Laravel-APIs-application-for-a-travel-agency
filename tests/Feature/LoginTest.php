<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // تعطيل الـ Observer أثناء الاختبار
        User::unsetEventDispatcher();
    }

    public function test_user_logged_in_with_correct_credentials(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token']);

    }

    public function test_user_logged_in_with_incorrect_credentials(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'nonexisting@user.com',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }
}
