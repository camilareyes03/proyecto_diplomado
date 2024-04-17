<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class RouteTest extends TestCase
{
    /**
     * Test home page route.
     *
     * @return void
     */
    public function testIndex()
    {
        $response = $this->get('/categorias');

        $response->assertStatus(302); // You will be redirected if not authenticated
    }

    /**
     * Test 'categorias' create route.
     *
     * @return void
     */
    public function testCreate()
    {
        $response = $this->get('/categorias/create');

        $response->assertStatus(302); // You will be redirected if not authenticated
    }

    /**que me devuelva el crear cuando este autentificado */

    
}
