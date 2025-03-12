<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_example()
    {
        $response = $this->get('/');

        $response->assertStatus(302); // 200から302に変更
    }
}
