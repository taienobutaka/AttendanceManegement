<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;

class UserSeeder extends Seeder
{
    public function run()
    {
        User::factory()->count(20)->create()->each(function ($user) {
            $attendance = Attendance::factory()->create(['user_id' => $user->id]);
            Rest::factory()->create(['attendance_id' => $attendance->id]);
        });
    }
}