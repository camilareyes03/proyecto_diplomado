<?php

namespace Tests\Feature;

use App\Models\Categoria;
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
     * Test 'categorias' index route when authenticated.
     *
     * @return void
     */
    public function testIndexWhenAuthenticated()
    {
        // Get an existing user
        $user = User::first();

        // Act as the user
        $response = $this->actingAs($user)->get('/categorias');

        $response->assertStatus(200);
        $response->assertViewIs('categoria.index');
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
        $response = $this->actingAs($user)->get('/categorias/create');

        $response->assertStatus(200);
        $response->assertViewIs('categoria.create');
    }



    public function testEditWhenAuthenticated()
    {
        $user = User::first();

        // Create a new category
        $categoria = Categoria::create([
            'nombre' => 'Original Category',
            'descripcion' => 'Original Description',
            // Add any other fields that are required to create a category
        ]);

        // Act as the user
        $response = $this->actingAs($user)->get("/categorias/{$categoria->id}/edit");

        $response->assertStatus(200);
        $response->assertViewIs('categoria.edit');
    }

    public function testUpdateWhenAuthenticated()
    {
        // Get an existing user
        $user = User::first();

        // Create a new category
        $categoria = Categoria::create([
            'nombre' => 'Original Category',
            'descripcion' => 'Original Description',
            // Add any other fields that are required to create a category
        ]);

        // Act as the user
        $response = $this->actingAs($user)->put("/categorias/{$categoria->id}", [
            'nombre' => 'Updated Category',
            // Add any other fields that are required to update a category
        ]);

        // Continue with the rest of your test...
    }


    public function testDestroyWhenAuthenticated()
    {
        // Get an existing user
        $user = User::first();

        // Create a new category to delete
        $categoria = Categoria::create([
            'nombre' => 'Category to delete',
            'descripcion' => 'Description for category to delete',

            // Add any other fields that are required to create a category
        ]);

        // Act as the user
        $response = $this->actingAs($user)->delete("/categorias/{$categoria->id}");

        $response->assertStatus(302); // Expect a redirect
        $response->assertRedirect('/categorias');

        // Assert the category was deleted
        $this->assertDatabaseMissing('categoria', [
            'id' => $categoria->id,
        ]);
    }
}
