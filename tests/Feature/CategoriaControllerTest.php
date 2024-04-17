<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CategoriaControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user in the database
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    /**
     * Test 'categorias' create route when authenticated.
     *
     * @return void
     */
    public function testCreateWhenAuthenticated()
    {
        // Get an existing user
        $user = User::first();

        // Act as the user
        $response = $this->actingAs($user)->get('/categorias');

        $response->assertStatus(200);
    }



}
