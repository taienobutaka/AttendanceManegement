<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * 本番デプロイ用: README 記載のログイン例ユーザーを冪等に用意する。
 * updateOrCreate のためデプロイを繰り返しても重複・失敗しない。
 */
class DemoLoginUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'yamada@example.com'],
            [
                'name' => '山田太郎',
                'password' => Hash::make('password'),
            ]
        );
    }
}
