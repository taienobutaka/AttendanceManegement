<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Test;

class TestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_test()
    {
        $test = Test::factory()->create();

        $this->assertDatabaseHas('tests', [
            'id' => $test->id,
            'name' => $test->name,
        ]);
    }
}
