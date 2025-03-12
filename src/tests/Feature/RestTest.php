<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

class RestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_creates_a_rest()
    {
        $user = User::factory()->create();
        $attendance = Attendance::factory()->create(['user_id' => $user->id]);
        $rest = Rest::factory()->create(['attendance_id' => $attendance->id]);

        $this->assertDatabaseHas('rests', [
            'id' => $rest->id,
            'attendance_id' => $attendance->id,
            'start_time' => $rest->start_time->format('H:i:s'),
            'end_time' => $rest->end_time->format('H:i:s'),
        ]);
    }
}
