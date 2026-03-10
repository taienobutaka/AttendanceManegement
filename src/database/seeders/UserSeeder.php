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
        // ダミー用の固定ユーザー（README に記載）
        $yamamoto = User::factory()->create([
            'name' => '山田太郎',
            'email' => 'yamada@example.com',
            'password' => Hash::make('password'),
        ]);
        $attendance = Attendance::factory()->create(['user_id' => $yamamoto->id]);
        Rest::factory()->create(['attendance_id' => $attendance->id]);

        // その他のユーザー
        User::factory()->count(19)->create()->each(function ($user) {
            $attendance = Attendance::factory()->create(['user_id' => $user->id]);
            Rest::factory()->create(['attendance_id' => $attendance->id]);
        });
    }
}