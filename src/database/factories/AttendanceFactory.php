<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User; // 追加
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition()
    {
        $startTime = $this->faker->dateTimeThisMonth();
        $endTime = (clone $startTime)->modify('+8 hours');

        return [
            'user_id' => User::factory(), // 修正
            'date' => $startTime->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];
    }
}
