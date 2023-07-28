<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A basic test example.
     */
    public function test_the_user_can_signup(): void
    {
        $response = $this->postJson('/api/signup',
            ['name' => 'test name', 'mobile' => '09109177831', 'password' => 'testPass']);

        $response
            ->assertStatus(200)
            ->assertJson([
                'message' => 'success',
            ]);
    }

    public function test_the_user_can_login(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/login',
            ['mobile' => '09109177831', 'password' => 'testPass']);

        $response
            ->assertStatus(200)
            ->assertSeeInOrder(['message', 'token']);
    }

    public function test_the_user_can_logout(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('access token')->plainTextToken;

        $response = $this->postJson('/api/logout', [], ['Authorization' => 'Bearer '.$token]);

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'success']);
    }
}
