<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    /**
     * A basic feature test example.
     */

     public function testBasicTest(){
        $response = $this->get('/');

        $response->assertStatus(200);
    }
    //probar rutas del login
    public function testLogin(){
        $response = $this->get('/login');
        $response->assertStatus(200);
    }
    
}
