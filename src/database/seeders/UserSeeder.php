<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\Rest;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run()
    {
        // 既存のユーザーシーディング
        User::factory()->count(20)->create()->each(function ($user) {
            $attendance = Attendance::factory()->create(['user_id' => $user->id]);
            Rest::factory()->create(['attendance_id' => $attendance->id]);
        });
    }
}