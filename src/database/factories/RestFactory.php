<?php

namespace Database\Factories;

use App\Models\Rest;
use App\Models\Attendance; // 追加
use Illuminate\Database\Eloquent\Factories\Factory;

class RestFactory extends Factory
{
    protected $model = Rest::class;

    public function definition()
    {
        $startTime = $this->faker->dateTimeThisMonth();
        $endTime = (clone $startTime)->modify('+1 hour');

        return [
            'attendance_id' => Attendance::factory(), // 修正
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];
    }
}
